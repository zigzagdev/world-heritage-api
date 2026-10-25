<?php

namespace App\Console\Commands;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Illuminate\Console\Command;

class AlgoliaSyncIndexSettings extends Command
{
    protected $signature = 'algolia:sync-index-settings {--dry-run}';

    protected $description = 'Apply the attributesForFaceting declared in code to the Algolia index settings.';

    /**
     * Every attribute the search Adapter filters or optional-filters on must
     * be declared here, or Algolia rejects the request with a facet error.
     * Attributes not used for filtering by the Adapter (e.g. country_names_jp,
     * region, study_regions) are kept as plain facets rather than
     * filterOnly() for now: whether the frontend needs their facet counts
     * has not been confirmed (see Domains/README.md).
     */
    public const ATTRIBUTES_FOR_FACETING = [
        'category',
        'country',
        'country_name_jp',
        'country_names_jp',
        'criteria',
        'is_endangered',
        'region',
        'state_party_codes',
        'study_region',
        'study_regions',
    ];

    public function handle(): int
    {
        $indexName = config('algolia.algolia_index');

        // Dry-run never talks to Algolia, so it must not depend on credentials being configured.
        if ($this->option('dry-run')) {
            $this->info('[dry-run] would set attributesForFaceting on index: ' . $indexName);
            $this->line(json_encode(self::ATTRIBUTES_FOR_FACETING, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $appId = config('algolia.algolia_app_id');
        $apiKey = config('algolia.algolia_write_api_key');

        if (!$appId || !$apiKey || !$indexName) {
            $this->error('Missing ALGOLIA_APP_ID / ALGOLIA_WRITE_API_KEY / ALGOLIA_INDEX');
            return self::FAILURE;
        }

        $client = SearchClient::create($appId, $apiKey);

        $res = $client->setSettings($indexName, [
            'attributesForFaceting' => self::ATTRIBUTES_FOR_FACETING,
        ]);

        $taskId = $res['taskID'] ?? null;
        if ($taskId !== null) {
            $client->waitForTask($indexName, $taskId);
        }

        $this->info("Done: attributesForFaceting synced on index={$indexName}");

        return self::SUCCESS;
    }
}
