<?php

namespace App\Packages\Domains\WorldHeritage\Tests;

use App\Console\Commands\AlgoliaSyncIndexSettings;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AlgoliaSyncIndexSettingsTest extends TestCase
{
    public function test_dry_run_prints_attributes_for_faceting_without_calling_algolia(): void
    {
        $exitCode = Artisan::call('algolia:sync-index-settings', ['--dry-run' => true]);

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();
        foreach (AlgoliaSyncIndexSettings::ATTRIBUTES_FOR_FACETING as $attribute) {
            $this->assertStringContainsString($attribute, $output);
        }
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
