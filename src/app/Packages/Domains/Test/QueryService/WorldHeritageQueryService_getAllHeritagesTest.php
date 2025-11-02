<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;

class WorldHeritageQueryService_getAllHeritagesTest extends TestCase
{

    private $queryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();
        $this->queryService = app(WorldHeritageQueryService::class);
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
            Image::truncate();
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
                'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
                'country' => 'Slovakia',
                'region' => 'Europe',
                'category' => 'Natural',
                'criteria' => ['ix'],
                'state_party' => null,
                'year_inscribed' => 2007,
                'area_hectares' => 99947.81,
                'buffer_zone_hectares' => 296275.8,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '氷期後のブナの自然拡散史を示すヨーロッパ各地の原生的ブナ林群から成る越境・連続資産。',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1133',
                'state_parties_codes' => [
                    'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
                ],
                'state_parties_meta' => [
                    'ALB' => ['is_primary' => false, 'inscription_year' => 2007],
                    'AUT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BEL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BIH' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BGR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'HRV' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CZE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'FRA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'DEU' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ITA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'MKD' => ['is_primary' => false, 'inscription_year' => 2007],
                    'POL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ROU' => ['is_primary' => false, 'inscription_year' => 2007],
                    'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SVN' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ESP' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CHE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
                ]
            ],
            [
                'id' => 1442,
                'official_name' => "Silk Roads: the Routes Network of Chang'an-Tianshan Corridor",
                'name' => "Silk·Roads:·Chang'an–Tianshan·Corridor",
                'name_jp' => 'シルクロード：長安－天山回廊の交易路網',
                'country' => 'China',
                'region' => 'Asia',
                'category' => 'Cultural',
                'criteria' => ['ii','iii','vi'],
                'state_party' => null,
                'year_inscribed' => 2014,
                'area_hectares' => 0.0,
                'buffer_zone_hectares' => 0.0,
                'is_endangered' => false,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'short_description' => '中国・カザフスタン・キルギスにまたがるオアシス都市や遺跡群で構成され、東西交流の歴史を物証する文化遺産群。',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1442',
                'state_parties' => ['CHN','KAZ','KGZ'],
                'state_parties_meta' => [
                    'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    public function test_fetch_data_check_type(): void
    {
        $result = $this->queryService->getAllHeritages();

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }

    public function test_fetch_data_check_value(): void
    {
        $result = $this->queryService->getAllHeritages();

        $this->assertCount(9, $result->toArray());
        $this->assertContains(1133, array_column($result->toArray(), 'id'));
        $this->assertContains(1442, array_column($result->toArray(), 'id'));

    }
}