<?php

namespace App\Console\Commands;

use App\Console\Concerns\LoadsJsonRows;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class ImportWorldHeritageSiteImagesFromJson extends Command
{

    use LoadsJsonRows;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world-heritage:import-images-json
        {--path=unesco/normalized/world_heritage_site_images.json : File or directory (local disk relative)}
        {--max=0 : 0 means no limit}
        {--batch=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import world_heritage_site_images from JSON into DB (upsert by site_id + url_hash)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = (string) $this->option('path');
        $max = (int) $this->option('max');
        $batchSize = max(1, (int) $this->option('batch'));

        $fullPath = $this->resolvePath($path);
        if (!file_exists($fullPath)) {
            $this->error("Path not found: {$fullPath}");
            return self::FAILURE;
        }

        $files = $this->collectJsonFiles($fullPath);
        if ($files === []) {
            $this->error("No JSON files found: {$fullPath}");
            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;
        $batch = [];
        $now = Carbon::now();

        foreach ($files as $filePath) {
            if ($max > 0 && $imported >= $max) {
                break;
            }

            $results = $this->loadRows($filePath);
            if ($results === null) {
                $this->warn("Skipped invalid JSON: {$filePath}");
                continue;
            }

            foreach ($results as $row) {
                if ($max > 0 && $imported >= $max) {
                    break;
                }
                if (!is_array($row)) {
                    $skipped++;
                    continue;
                }

                $mapped = $this->mapRow($row);
                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                $mapped['created_at'] ??= $now;
                $mapped['updated_at'] = $now;
                $batch[] = $mapped;

                if (count($batch) >= $batchSize) {
                    $imported += $this->flushBatch($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            $imported += $this->flushBatch($batch);
        }

        $this->info("Imported/updated {$imported} records. Skipped {$skipped} items.");
        return self::SUCCESS;
    }

    private function mapRow(array $row): ?array
    {
        $siteId = $row['world_heritage_site_id'] ?? null;
        $url = $row['url'] ?? null;

        if (!is_numeric($siteId) || !is_string($url)) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        return [
            'world_heritage_site_id' => (int) $siteId,
            'url' => $url,
            'url_hash' => hash('sha256', $url),
            'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'is_primary' => empty($row['is_primary']) ? 0 : 1,
        ];
    }

    private function flushBatch(array $batch): int
    {
        DB::table('world_heritage_site_images')->upsert(
            $batch,
            ['world_heritage_site_id', 'url_hash'],
            ['url', 'sort_order', 'is_primary', 'updated_at']
        );
        return count($batch);
    }

    private function collectJsonFiles(string $fullPath): array
    {
        if (is_file($fullPath)) {
            return str_ends_with($fullPath, '.json') ? [$fullPath] : [];
        }

        $files = [];
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.json')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }
}