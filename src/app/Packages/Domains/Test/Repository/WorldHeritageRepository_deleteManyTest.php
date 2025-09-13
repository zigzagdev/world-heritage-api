<?php

namespace App\Packages\Domains\Test\Repository;

use Tests\TestCase;
use App\Packages\Domains\WorldHeritageRepository;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use App\Models\Country;

class WorldHeritageRepository_deleteManyTest extends TestCase
{

    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->repository = app(WorldHeritageRepository::class);
        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            WorldHeritage::truncate();
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return [
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'name_jp' => null,
                'country' => 'Slovakia',
                'region' => 'Europe',
                'category' => 'natural',
                'criteria' => ['ix'],
                'state_party' => null,
                'year_inscribed' => 2007,
                'area_hectares' => 99947.81,
                'buffer_zone_hectares' => 296275.8,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
                'state_parties' => [
                    'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
                ],
                'state_parties_meta' => [
                    'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
                    'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
                    'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
                    'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
                    'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
                    'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
                    'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
                    'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
                    'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
                    'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
                    'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
                    'POL' => ['is_primary' => false, 'inscription_year' => 2021],
                    'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
                    'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
                    'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
                    'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
                    'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
                ]
            ],
            [
                'id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk Roads: Chang'an–Tianshan Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China, Kazakhstan, Kyrgyzstan',
                'region' => 'Asia',
                'category' => 'cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 0.0,
                'buffer_zone_hectares' => 0.0,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => 'Transnational Silk Road corridor across China, Kazakhstan and Kyrgyzstan illustrating exchange of goods, ideas and beliefs.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442/',
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    public function test_delete_ok(): void
    {
        $this->repository->deleteManyHeritages(array_column(self::arrayData(), 'id'));

        $this->assertDatabaseMissing('world_heritage_sites', [
            'id' => 1133,
        ]);
        $this->assertDatabaseMissing('world_heritage_sites', [
            'id' => 1442,
        ]);
        $this->assertDatabaseMissing('site_state_parties', [
            'world_heritage_site_id' => 1133,
        ]);
        $this->assertDatabaseMissing('site_state_parties', [
            'world_heritage_site_id' => 1442,
        ]);
    }
}