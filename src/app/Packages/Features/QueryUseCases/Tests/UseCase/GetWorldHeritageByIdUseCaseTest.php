<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetWorldHeritageByIdUseCaseTest extends TestCase
{
    private $queryService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->queryService = app(WorldHeritageQueryService::class);
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
            'image_url' => '',
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
        ];
    }

    public function test_use_case(): void
    {
        $useCase = new GetWorldHeritageByIdUseCase($this->queryService);

        $result = $useCase->handle(self::arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }
}