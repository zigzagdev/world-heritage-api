<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Models\Country;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;
use Database\Seeders\CountrySeeder;
use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldManyHeritagesUseCase;

class CreateWorldHeritagesUseCaseTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
        $seeder->run();
        $this->repository = Mockery::mock(WorldHeritageRepositoryInterface::class);
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

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface
        ::class);

        $mock->shouldReceive('insertHeritages')
            ->with(Mockery::type(WorldHeritageEntityCollection::class))
            ->andReturnUsing(function (WorldHeritageEntityCollection $entities) {
                return $entities;
            });

        return $mock;
    }

    private static function arrayData(): array
    {
        return [
            [
                'id' => 1,
                'unesco_id' => '1133',
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
                    'AL','AT','BE','BA','BG','HR','CZ','FR','DE','IT','MK','PL','RO','SK','SI','ES','CH','UA'
                ],
                'state_parties_meta' => [
                    'AL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'AT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BA' => ['is_primary' => false, 'inscription_year' => 2007],
                    'BG' => ['is_primary' => false, 'inscription_year' => 2007],
                    'HR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CZ' => ['is_primary' => false, 'inscription_year' => 2007],
                    'FR' => ['is_primary' => false, 'inscription_year' => 2007],
                    'DE' => ['is_primary' => false, 'inscription_year' => 2007],
                    'IT' => ['is_primary' => false, 'inscription_year' => 2007],
                    'MK' => ['is_primary' => false, 'inscription_year' => 2007],
                    'PL' => ['is_primary' => false, 'inscription_year' => 2007],
                    'RO' => ['is_primary' => false, 'inscription_year' => 2007],
                    'SK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SI' => ['is_primary' => false, 'inscription_year' => 2007],
                    'ES' => ['is_primary' => false, 'inscription_year' => 2007],
                    'CH' => ['is_primary' => false, 'inscription_year' => 2007],
                    'UA' => ['is_primary' => false, 'inscription_year' => 2007],
                ],
            ],
            [
                'id' => 2,
                'unesco_id' => '1442',
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
                'state_parties' => ['CN','KZ','KG'],
                'state_parties_meta' => [
                    'CN' => ['is_primary' => true,  'inscription_year' => 2014],
                    'KZ' => ['is_primary' => false, 'inscription_year' => 2014],
                    'KG' => ['is_primary' => false, 'inscription_year' => 2014],
                ],
            ],
        ];
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new CreateWorldManyHeritagesUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(self::arrayData());

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new CreateWorldManyHeritagesUseCase(
            $this->mockRepository()
        );

        $result = $useCase->handle(self::arrayData());

        foreach ($result->toArray() as $key => $value) {
            $this->assertSame(self::arrayData()[$key]['id'], $value['id']);
            $this->assertSame(self::arrayData()[$key]['unesco_id'], $value['unescoId']);
            $this->assertSame(self::arrayData()[$key]['official_name'], $value['officialName']);
            $this->assertSame(self::arrayData()[$key]['name'], $value['name']);
            $this->assertSame(self::arrayData()[$key]['name_jp'], $value['nameJp']);
            $this->assertSame(self::arrayData()[$key]['country'], $value['country']);
            $this->assertSame(self::arrayData()[$key]['region'], $value['region']);
            $this->assertSame(self::arrayData()[$key]['state_party'], $value['stateParty']);
            $this->assertSame(self::arrayData()[$key]['category'], $value['category']);
            $this->assertSame(self::arrayData()[$key]['criteria'], $value['criteria']);
            $this->assertSame(self::arrayData()[$key]['year_inscribed'], $value['yearInscribed']);
            $this->assertSame(self::arrayData()[$key]['area_hectares'], $value['areaHectares']);
            $this->assertSame(self::arrayData()[$key]['buffer_zone_hectares'], $value['bufferZoneHectares']);
            $this->assertSame(self::arrayData()[$key]['is_endangered'], $value['isEndangered']);
            $this->assertSame(self::arrayData()[$key]['latitude'], $value['latitude']);
            $this->assertSame(self::arrayData()[$key]['longitude'], $value['longitude']);
            $this->assertSame(self::arrayData()[$key]['short_description'], $value['shortDescription']);
            $this->assertSame(self::arrayData()[$key]['image_url'], $value['imageUrl']);
            $this->assertSame(self::arrayData()[$key]['unesco_site_url'], $value['unescoSiteUrl']);
        }
    }
}
