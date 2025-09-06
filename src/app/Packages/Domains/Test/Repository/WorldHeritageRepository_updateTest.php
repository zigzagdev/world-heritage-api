<?php

namespace App\Packages\Domains\Test\Repository;

use App\Models\Country;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepository;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;

class WorldHeritageRepository_updateTest extends TestCase
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

    private static function requestSingleContent(): array
    {
        return [
            'id' => 1418,
            'official_name' => 'Fujisan, sacred place and source of artistic inspiration',
            'name' => 'Fujisan',
            'name_jp' => '富士山—信仰の対象と芸術の源泉(更新した。)',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => null,
            'category' => 'Cultural',
            'criteria' => ['iii', 'vi'],
            'year_inscribed' => 2013,
            'area_hectares' => null,
            'buffer_zone_hectares' => null,
            'is_endangered' => false,
            'latitude' => null,
            'longitude' => null,
            'short_description' => '日本の象徴たる霊峰。信仰・芸術・登拝文化に深い影響を与えた文化的景観。',
            'image_url' => null,
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418',
            'state_parties' => ['JPN'],
            'state_parties_meta' => [
                'JPN' => [
                    'is_primary' => true,
                    'inscription_year' => 2013,
                ],
            ]
        ];
    }

    private static function requestMultiContent(): array
    {
        return [
            'id' => 1418,
            'official_name' => 'Fujisan, sacred place and source of artistic inspiration',
            'name' => 'Fujisan',
            'name_jp' => '富士山—信仰の対象と芸術の源泉',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => null,
            'category' => 'Cultural',
            'criteria' => ['iii', 'vi'],
            'year_inscribed' => 2013,
            'area_hectares' => null,
            'buffer_zone_hectares' => null,
            'is_endangered' => false,
            'latitude' => null,
            'longitude' => null,
            'short_description' => '日本の象徴たる霊峰。信仰・芸術・登拝文化に深い影響を与えた文化的景観。',
            'image_url' => null,
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418',
            'state_parties' => ['FRA'],
            'state_parties_meta' => [
                'FRA' => [
                    'is_primary' => true,
                    'inscription_year' => 5000,
                ],
            ],
        ];
    }

    public function test_update_single_ok_check_type(): void
    {
        $entity = new WorldHeritageEntity(
            self::requestSingleContent()['id'],
            self::requestSingleContent()['official_name'],
            self::requestSingleContent()['name'],
            self::requestSingleContent()['country'],
            self::requestSingleContent()['region'],
            self::requestSingleContent()['category'],
            self::requestSingleContent()['year_inscribed'],
            self::requestSingleContent()['latitude'],
            self::requestSingleContent()['longitude'],
            self::requestSingleContent()['is_endangered'],
            self::requestSingleContent()['name_jp'],
            self::requestSingleContent()['state_party'],
            self::requestSingleContent()['criteria'],
            self::requestSingleContent()['area_hectares'],
            self::requestSingleContent()['buffer_zone_hectares'],
            self::requestSingleContent()['short_description'],
            self::requestSingleContent()['image_url'],
            self::requestSingleContent()['unesco_site_url'],
            self::requestSingleContent()['state_parties'],
            self::requestSingleContent()['state_parties_meta']
        );

        $result = $this->repository->updateOneHeritage($entity);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }

    public function test_update_multi_ok_check_type(): void
    {
        $entity = new WorldHeritageEntity(
            self::requestMultiContent()['id'],
            self::requestMultiContent()['official_name'],
            self::requestMultiContent()['name'],
            self::requestMultiContent()['country'],
            self::requestMultiContent()['region'],
            self::requestMultiContent()['category'],
            self::requestMultiContent()['year_inscribed'],
            self::requestMultiContent()['latitude'],
            self::requestMultiContent()['longitude'],
            self::requestMultiContent()['is_endangered'],
            self::requestMultiContent()['name_jp'],
            self::requestMultiContent()['state_party'],
            self::requestMultiContent()['criteria'],
            self::requestMultiContent()['area_hectares'],
            self::requestMultiContent()['buffer_zone_hectares'],
            self::requestMultiContent()['short_description'],
            self::requestMultiContent()['image_url'],
            self::requestMultiContent()['unesco_site_url'],
            self::requestMultiContent()['state_parties'],
            self::requestMultiContent()['state_parties_meta']
        );

        $result = $this->repository->updateOneHeritage($entity);

        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
    }

    public function test_update_single_ok_check_value(): void
    {
        $beforeChange = WorldHeritage::query()->where('id', self::requestSingleContent()['id'])->first()->name_jp;

        $entity = new WorldHeritageEntity(
            self::requestSingleContent()['id'],
            self::requestSingleContent()['official_name'],
            self::requestSingleContent()['name'],
            self::requestSingleContent()['country'],
            self::requestSingleContent()['region'],
            self::requestSingleContent()['category'],
            self::requestSingleContent()['year_inscribed'],
            self::requestSingleContent()['latitude'],
            self::requestSingleContent()['longitude'],
            self::requestSingleContent()['is_endangered'],
            self::requestSingleContent()['name_jp'],
            self::requestSingleContent()['state_party'],
            self::requestSingleContent()['criteria'],
            self::requestSingleContent()['area_hectares'],
            self::requestSingleContent()['buffer_zone_hectares'],
            self::requestSingleContent()['short_description'],
            self::requestSingleContent()['image_url'],
            self::requestSingleContent()['unesco_site_url'],
            self::requestSingleContent()['state_parties'] ?? [],
            self::requestSingleContent()['state_parties_meta'] ?? []
        );

        $result = $this->repository->updateOneHeritage($entity);

        $this->assertSame(self::requestSingleContent()['id'], $result->getId());
        $this->assertSame(self::requestSingleContent()['official_name'], $result->getOfficialName());
        $this->assertSame(self::requestSingleContent()['name'], $result->getName());
        $this->assertSame(self::requestSingleContent()['name_jp'], $result->getNameJp());
        $this->assertSame(self::requestSingleContent()['country'], $result->getCountry());
        $this->assertSame(self::requestSingleContent()['region'], $result->getRegion());
        $this->assertSame(self::requestSingleContent()['category'], $result->getCategory());
        $this->assertSame(self::requestSingleContent()['criteria'], $result->getCriteria());
        $this->assertSame(self::requestSingleContent()['year_inscribed'], $result->getYearInscribed());
        $this->assertSame(self::requestSingleContent()['area_hectares'], $result->getAreaHectares());
        $this->assertSame(self::requestSingleContent()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertSame(self::requestSingleContent()['is_endangered'], $result->isEndangered());
        $this->assertSame(self::requestSingleContent()['latitude'], $result->getLatitude());
        $this->assertSame(self::requestSingleContent()['longitude'], $result->getLongitude());
        $this->assertSame(self::requestSingleContent()['short_description'], $result->getShortDescription());
        $this->assertSame(self::requestSingleContent()['image_url'], $result->getImageUrl());
        $this->assertSame(self::requestSingleContent()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertNotSame($beforeChange, $result->getNameJp());
    }

    public function test_update_multi_ok_check_value(): void
    {
        $entity = new WorldHeritageEntity(
            self::requestMultiContent()['id'],
            self::requestMultiContent()['official_name'],
            self::requestMultiContent()['name'],
            self::requestMultiContent()['country'],
            self::requestMultiContent()['region'],
            self::requestMultiContent()['category'],
            self::requestMultiContent()['year_inscribed'],
            self::requestMultiContent()['latitude'],
            self::requestMultiContent()['longitude'],
            self::requestMultiContent()['is_endangered'],
            self::requestMultiContent()['name_jp'],
            self::requestMultiContent()['state_party'],
            self::requestMultiContent()['criteria'],
            self::requestMultiContent()['area_hectares'],
            self::requestMultiContent()['buffer_zone_hectares'],
            self::requestMultiContent()['short_description'],
            self::requestMultiContent()['image_url'],
            self::requestMultiContent()['unesco_site_url'],
            self::requestMultiContent()['state_parties'] ?? [],
            self::requestMultiContent()['state_parties_meta'] ?? []
        );

        $result = $this->repository->updateOneHeritage($entity);

        $this->assertDatabaseHas(
            'world_heritage_sites',
            [
                'id' => $result->getId(),
                'official_name' => $result->getOfficialName(),
                'name' => $result->getName(),
                'name_jp' => $result->getNameJp(),
                'country' => $result->getCountry(),
                'region' => $result->getRegion(),
                'state_party' => $result->getStateParty(),
                'category' => $result->getCategory(),
                'year_inscribed' => $result->getYearInscribed(),
                'area_hectares' => $result->getAreaHectares(),
                'buffer_zone_hectares' => $result->getBufferZoneHectares(),
                'is_endangered' => $result->isEndangered(),
                'latitude' => $result->getLatitude(),
                'longitude' => $result->getLongitude(),
                'short_description' => $result->getShortDescription(),
                'image_url' => $result->getImageUrl(),
                'unesco_site_url' => $result->getUnescoSiteUrl(),
            ]
        );

        $this->assertTrue(
            DB::table('world_heritage_sites')
                ->where('id', 1418)
                ->whereJsonContains('criteria', 'iii')
                ->whereJsonContains('criteria', 'vi')
                ->exists()
        );

        $this->assertDatabaseHas(
            'site_state_parties',
            [
                'world_heritage_site_id' => $result->getId(),
                'state_party_code' => 'FRA',
                'is_primary' => true,
                'inscription_year' => 5000,
            ]
        );

    }
}
