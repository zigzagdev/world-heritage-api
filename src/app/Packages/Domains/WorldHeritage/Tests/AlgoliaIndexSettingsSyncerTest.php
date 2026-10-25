<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Packages\Domains\WorldHeritage\Adapter\AlgoliaIndexSettingsSyncer;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

class AlgoliaIndexSettingsSyncerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DoesNotPerformAssertions]
    public function test_waits_for_the_indexing_task_when_algolia_returns_a_task_id(): void
    {
        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('setSettings')
            ->once()
            ->with('world_heritages', ['attributesForFaceting' => ['category', 'country']])
            ->andReturn(['taskID' => 42]);
        $client->shouldReceive('waitForTask')->once()->with('world_heritages', 42);

        $syncer = new AlgoliaIndexSettingsSyncer($client);
        $syncer->syncAttributesForFaceting('world_heritages', ['category', 'country']);
    }

    #[DoesNotPerformAssertions]
    public function test_does_not_wait_when_algolia_returns_no_task_id(): void
    {
        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('setSettings')->once()->andReturn([]);
        $client->shouldNotReceive('waitForTask');

        $syncer = new AlgoliaIndexSettingsSyncer($client);
        $syncer->syncAttributesForFaceting('world_heritages', ['category']);
    }
}
