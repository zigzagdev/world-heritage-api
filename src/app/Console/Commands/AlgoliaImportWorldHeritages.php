<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorldHeritage;
use Algolia\AlgoliaSearch\Api\SearchClient;


class AlgoliaImportWorldHeritages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'algolia:import-world-heritages
        {--chunk=500}
        {--truncate}
        {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upsert world heritages into Algolia index.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $appId = config('algolia.algolia_app_id');
        $apiKey = config('algolia.algolia_write_api_key');
        $indexName = config('algolia.algolia_index');

        if (!$appId || !$apiKey || !$indexName) {
            $this->error('Missing ALGOLIA_APP_ID / ALGOLIA_WRITE_API_KEY / ALGOLIA_INDEX');
            return self::FAILURE;
        }

        $chunk = max(1, (int)$this->option('chunk'));
        $dryRun = (bool)$this->option('dry-run');
        $truncate = (bool)$this->option('truncate');

        $client = SearchClient::create($appId, $apiKey);

        // writing code to test this
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
                'thumbnail',
                'countries' => function ($query) {
                    $query->select(['countries.state_party_code', 'countries.name_jp']);
                },
            ])
            ->select([
                'world_heritage_sites.id',
                'official_name',
                'name',
                'world_heritage_sites.name_jp',
                'world_heritage_sites.region',
                'country',
                'category',
                'year_inscribed',
                'is_endangered',
                'image_url',
            ])
            ->chunkById($chunk, function ($rows) use ($client, $indexName, $dryRun, &$processed) {
                $objects = [];

                foreach ($rows as $row) {

                    $statePartyCodes = $row->countries->pluck('state_party_code')->toArray();
                    $countryNamesJp = $row->countries->pluck('name_jp')->toArray();

                    $objects[] = [
                        'objectID' => (string)$row->id,
                        'official_name' => (string)$row->official_name,
                        'name' => (string)$row->name,
                        'name_jp' => (string)$row->name_jp,
                        'country' => $row->country !== null ? (string)$row->country : null,
                        'country_name_jp' => $row->countries->first()?->name_jp,
                        'region' => (string)$row->region,
                        'category' => (string)$row->category,
                        'year_inscribed' => (int)$row->year_inscribed,
                        'is_endangered' => (bool)$row->is_endangered,
                        'thumbnail_url' => $row->image_url !== null ? (string)$row->image_url : null,
                        'country_names_jp' => $countryNamesJp,
                        'state_party_codes' => $statePartyCodes,
                    ];
                }

                if ($dryRun) {
                    $processed += count($objects);
                    return;
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
