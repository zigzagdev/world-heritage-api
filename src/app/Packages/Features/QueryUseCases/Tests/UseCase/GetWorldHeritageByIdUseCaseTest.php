<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Models\Image;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\Ports\SignedUrlPort;
use Mockery;

class GetWorldHeritageByIdUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

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
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function mockQueryService(): WorldHeritageQueryServiceInterface
    {
        $queryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $queryService
            ->shouldReceive('getHeritageById')
            ->andReturn(new WorldHeritageDto(
                self::arrayData()['id'],
                self::arrayData()['official_name'],
                self::arrayData()['name'],
                self::arrayData()['country'],
                self::arrayData()['country_name_jp'] ?? null,
                self::arrayData()['region'],
                self::arrayData()['category'],
                self::arrayData()['year_inscribed'],
                self::arrayData()['latitude'],
                self::arrayData()['longitude'],
                self::arrayData()['is_endangered'],
                self::arrayData()['heritage_name_jp'] ?? null,
                self::arrayData()['state_party'],
                self::arrayData()['criteria'],
                self::arrayData()['area_hectares'],
                self::arrayData()['buffer_zone_hectares'],
                self::arrayData()['short_description'],
                null,
                null,
                self::arrayData()['unesco_site_url'],
                self::arrayData()['state_party_codes'],
                self::arrayData()['state_parties_meta'],
            ));

        return $queryService;
    }

    private static function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => 'Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe',
            'name' => 'Ancient and Primeval Beech Forests',
            'heritage_name_jp' => 'カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林',
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
            'state_party_codes' => [
                'ALB',
                'AUT',
                'BEL',
                'BIH',
                'BGR',
                'HRV',
                'CZE',
                'FRA',
                'DEU',
                'ITA',
                'MKD',
                'POL',
                'ROU',
                'SVK',
                'SVN',
                'ESP',
                'CHE',
                'UKR',
            ],
            'state_parties_meta' => [
                'ALB' => ['is_primary' => false],
                'AUT' => ['is_primary' => false],
                'BEL' => ['is_primary' => false],
                'BIH' => ['is_primary' => false],
                'BGR' => ['is_primary' => false],
                'HRV' => ['is_primary' => false],
                'CZE' => ['is_primary' => false],
                'FRA' => ['is_primary' => false],
                'DEU' => ['is_primary' => false],
                'ITA' => ['is_primary' => false],
                'MKD' => ['is_primary' => false],
                'POL' => ['is_primary' => false],
                'ROU' => ['is_primary' => false],
                'SVK' => ['is_primary' => true],
                'SVN' => ['is_primary' => false],
                'ESP' => ['is_primary' => false],
                'CHE' => ['is_primary' => false],
                'UKR' => ['is_primary' => false],
            ],
        ];
    }

    public function test_use_case(): void
    {
        $useCase = new GetWorldHeritageByIdUseCase($this->mockQueryService());

        $result = $useCase->handle(self::arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }
}