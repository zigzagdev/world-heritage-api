<?php

namespace App\Packages\Domains\WorldHeritage\Adapter;

use Algolia\AlgoliaSearch\Api\SearchClient;

class AlgoliaIndexSettingsSyncer
{
    public function __construct(
        private SearchClient $client,
    ) {}

    public function syncAttributesForFaceting(string $indexName, array $attributesForFaceting): void
    {
        $res = $this->client->setSettings($indexName, [
            'attributesForFaceting' => $attributesForFaceting,
        ]);

        $taskId = $res['taskID'] ?? null;
        if ($taskId !== null) {
            $this->client->waitForTask($indexName, $taskId);
        }
    }
}
