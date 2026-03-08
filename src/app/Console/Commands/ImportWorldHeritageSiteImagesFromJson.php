<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class ImportWorldHeritageSiteImagesFromJson extends Command
{
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
        $max  = (int) $this->option('max');
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
        $skipped  = 0;
        $batch = [];
        $now = Carbon::now();

        foreach ($files as $filePath) {
            if ($max > 0 && $imported >= $max) break;

            $results = $this->loadResultsFromJsonFile($filePath);
            if ($results === null) {
                $this->warn("Skipped invalid JSON: {$filePath}");
                continue;
            }

            foreach ($results as $row) {
                if ($max > 0 && $imported >= $max) break;
                if (!is_array($row)) { $skipped++; continue; }

                $mapped = $this->mapRow($row);
                if ($mapped === null) { $skipped++; continue; }

                $mapped['updated_at'] = $now;
                $mapped['created_at'] ??= $now;

                $batch[] = $mapped;

                if (count($batch) >= $batchSize) {
                    $imported += $this->flushBatch($batch);
                    $batch = [];
                }
            }
        }

        if ($batch) {
            $imported += $this->flushBatch($batch);
        }

        $this->info("Imported/updated {$imported} records. Skipped {$skipped} items.");
        return self::SUCCESS;
    }

    private function mapRow(array $row): ?array
    {
        $siteId = $row['world_heritage_site_id'] ?? null;
        $url = $row['url'] ?? null;

        if (!is_numeric($siteId)) return null;
        $siteId = (int) $siteId;

        if (!is_string($url)) return null;

        $url = trim($url);
        if ($url === '') return null;

        $urlHash = hash('sha256', $url);

        return [
            'world_heritage_site_id' => $siteId,
            'url' => $url,
            'url_hash' => $urlHash,
            'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'is_primary' => !empty($row['is_primary']) ? 1 : 0,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     */
    private function flushBatch(array $batch): int
    {
        DB::table('world_heritage_site_images')->upsert(
            $batch,
            ['world_heritage_site_id', 'url_hash'],
            ['url', 'sort_order', 'is_primary', 'updated_at']
        );

        return count($batch);
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') return $path;

        if (str_starts_with($path, '/')) return $path;
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) return $path;

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/app/')) {
            $path = substr($path, strlen('storage/app/'));
        }

        if (str_starts_with($path, 'private/')) {
            $path = substr($path, strlen('private/'));
        }

        return Storage::disk('local')->path($path);
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

    private function loadResultsFromJsonFile(string $filePath): ?array
    {
        $raw = @file_get_contents($filePath);
        if ($raw === false) return null;

        $json = json_decode($raw, true);
        if (!is_array($json)) return null;

        if (array_key_exists('results', $json)) {
            return is_array($json['results']) ? $json['results'] : null;
        }

        return $json;
    }
}