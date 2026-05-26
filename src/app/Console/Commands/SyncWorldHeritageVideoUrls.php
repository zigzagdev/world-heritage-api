<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncWorldHeritageVideoUrls extends Command
{
    protected $signature = 'world-heritage:sync-video-urls
        {--limit=100 : Records per API request}
        {--dry-run : Show counts without updating DB}';

    protected $description = 'Fetch main_video_url from data.unesco.org API and update world_heritage_sites';

    private const API_URL = 'https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/records';

    public function handle(): int
    {
        $limit   = max(1, (int) $this->option('limit'));
        $dryRun  = (bool) $this->option('dry-run');

        $first = $this->fetch(1, 0);
        if ($first === null) {
            return self::FAILURE;
        }

        $total   = (int) ($first['total_count'] ?? 0);
        $offset  = 0;
        $updated = 0;
        $skipped = 0;

        $this->info("Total UNESCO records: {$total}");

        while ($offset < $total) {
            $response = $this->fetch($limit, $offset);
            if ($response === null) {
                return self::FAILURE;
            }

            $results = $response['results'] ?? [];
            if ($results === []) {
                break;
            }

            foreach ($results as $row) {
                $idNo     = (int) ($row['id_no'] ?? 0);
                $videoUrl = $row['main_video_url'] ?? null;

                if ($idNo === 0) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    DB::table('world_heritage_sites')
                        ->where('id', $idNo)
                        ->update(['main_video_url' => $videoUrl]);
                }

                $updated++;
            }

            $offset += count($results);
            $this->line("Processed {$offset}/{$total}...");
        }

        if ($dryRun) {
            $this->info("Dry-run: would update {$updated} records, skipped {$skipped}.");
        } else {
            $this->info("Done. Updated {$updated} records, skipped {$skipped}.");
        }

        return self::SUCCESS;
    }

    private function fetch(int $limit, int $offset): ?array
    {
        $response = Http::acceptJson()
            ->retry(3, 500)
            ->get(self::API_URL, [
                'select'   => 'id_no,main_video_url',
                'limit'    => $limit,
                'offset'   => $offset,
                'order_by' => 'id_no asc',
            ]);

        if ($response->failed()) {
            $this->error("UNESCO API error: HTTP {$response->status()} offset={$offset}");
            return null;
        }

        return $response->json();
    }
}