<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Console\Commands\AlgoliaSyncIndexSettings;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class AlgoliaSyncIndexSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_dry_run_prints_attributes_for_faceting_without_calling_algolia(): void
    {
        $exitCode = Artisan::call('algolia:sync-index-settings', ['--dry-run' => true]);

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();
        foreach (AlgoliaSyncIndexSettings::ATTRIBUTES_FOR_FACETING as $attribute) {
            $this->assertStringContainsString($attribute, $output);
        }
    }

    public function test_fails_when_write_credentials_are_missing(): void
    {
        config(['algolia.algolia_write_api_key' => null]);

        $exitCode = Artisan::call('algolia:sync-index-settings');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Missing', Artisan::output());
    }

    public function test_syncs_attributes_for_faceting_when_credentials_are_present(): void
    {
        config([
            'algolia.algolia_app_id' => 'dummy-app',
            'algolia.algolia_write_api_key' => 'dummy-key',
            'algolia.algolia_index' => 'world_heritages',
        ]);

        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('setSettings')
            ->once()
            ->with('world_heritages', ['attributesForFaceting' => AlgoliaSyncIndexSettings::ATTRIBUTES_FOR_FACETING])
            ->andReturn(['taskID' => 123]);
        $client->shouldReceive('waitForTask')->once()->with('world_heritages', 123);

        $this->app->instance(SearchClient::class, $client);

        $exitCode = Artisan::call('algolia:sync-index-settings');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Done', Artisan::output());
    }

    public function test_attributes_for_faceting_covers_every_attribute_the_adapter_filters_on(): void
    {
        // Every attribute AlgoliaWorldHeritageSearchAdapter uses in $filters
        // or $optionalFilters must be declared here, or Algolia rejects the
        // facet filter request.
        $usedByAdapter = [
            'state_party_codes',
            'country',
            'country_name_jp',
            'study_region',
            'category',
            'criteria',
            'is_endangered',
        ];

        foreach ($usedByAdapter as $attribute) {
            $this->assertContains($attribute, AlgoliaSyncIndexSettings::ATTRIBUTES_FOR_FACETING);
        }
    }
}
