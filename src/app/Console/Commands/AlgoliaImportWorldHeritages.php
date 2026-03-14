<?php

namespace App\Console\Commands;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Models\WorldHeritage;
use Illuminate\Console\Command;

class AlgoliaImportWorldHeritages extends Command
{
    protected $signature = 'algolia:import-world-heritages
        {--chunk=500}
        {--truncate}
        {--dry-run}';

    protected $description = 'Upsert world heritages into Algolia index.';

    public function handle(): int
    {
        $appId = config('algolia.algolia_app_id');
        $apiKey = config('algolia.algolia_write_api_key');
        $indexName = config('algolia.algolia_index');

        if (!$appId || !$apiKey || !$indexName) {
            $this->error('Missing ALGOLIA_APP_ID / ALGOLIA_WRITE_API_KEY / ALGOLIA_INDEX');
            return self::FAILURE;
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $truncate = (bool) $this->option('truncate');
        $processed = 0;

        $client = SearchClient::create($appId, $apiKey);

        if ($truncate) {
            if ($dryRun) {
                $this->info('[dry-run] would clear index');
            } else {
                $this->warn("Clearing index: {$indexName}");

                $res = $client->clearObjects(indexName: $indexName);
                $taskId = $res['taskID'] ?? null;

                if ($taskId !== null) {
                    $client->waitForTask($indexName, $taskId);
                }
            }
        }

        WorldHeritage::query()
            ->with([
                'countries',
            ])
            ->select([
                'world_heritage_sites.id',
                'world_heritage_sites.official_name',
                'world_heritage_sites.name',
                'world_heritage_sites.name_jp',
                'world_heritage_sites.region',
                'world_heritage_sites.study_region',
                'world_heritage_sites.category',
                'world_heritage_sites.year_inscribed',
                'world_heritage_sites.is_endangered',
                'world_heritage_sites.image_url',
            ])
            ->chunkById($chunk, function ($rows) use ($client, $indexName, $dryRun, &$processed) {
                $objects = [];

                foreach ($rows as $row) {
                    $countries = $row->countries
                        ->filter(fn ($country) => $country->state_party_code !== null)
                        ->values();

                    $statePartyCodes = $countries
                        ->pluck('state_party_code')
                        ->filter()
                        ->values()
                        ->toArray();

                    $countryNamesEn = $countries
                        ->pluck('name_en')
                        ->filter()
                        ->values()
                        ->toArray();

                    $countryNamesJp = $countries
                        ->pluck('name_jp')
                        ->filter()
                        ->values()
                        ->toArray();

                    $countryCount = $countries->count();

                    $country = null;
                    $countryNameJp = null;

                    if ($countryCount === 1) {
                        $country = $countryNamesEn[0] ?? null;
                        $countryNameJp = $countryNamesJp[0] ?? null;
                    }

                    $objects[] = [
                        'objectID' => (string) $row->id,
                        'id' => (int) $row->id,
                        'official_name' => (string) $row->official_name,
                        'name' => (string) $row->name,
                        'name_jp' => (string) $row->name_jp,
                        'country' => $country,
                        'country_name_jp' => $countryNameJp,
                        'region' => (string) $row->region,
                        'study_region' => (string) $row->study_region,
                        'category' => (string) $row->category,
                        'year_inscribed' => $row->year_inscribed !== null ? (int) $row->year_inscribed : null,
                        'is_endangered' => (bool) $row->is_endangered,
                        'thumbnail_url' => $row->image_url !== null ? (string) $row->image_url : null,
                        'state_party_codes' => $countryCount > 1 ? $statePartyCodes : [],
                        'country_names_jp' => $countryCount > 1 ? $countryNamesJp : [],
                    ];
                }

                if ($dryRun) {
                    if ($processed === 0 && isset($objects[0])) {
                        $this->line(json_encode($objects[0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    }

                    $processed += count($objects);
                    return;
                }
                if ((int) $row->id === 1133) {
                    dd([
                        'state_party_codes' => $statePartyCodes,
                        'country_names_jp' => $countryNamesJp,
                        'object' => end($objects),
                    ]);
                }

                $res = $client->saveObjects(
                    indexName: $indexName,
                    objects: $objects
                );

                $taskId = $res['taskID'] ?? null;

                if ($taskId !== null) {
                    $client->waitForTask($indexName, $taskId);
                }

                $processed += count($objects);
            });

        $this->info("Done: processed={$processed}");

        return self::SUCCESS;
    }
}