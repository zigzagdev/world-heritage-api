<?php

namespace App\Packages\Domains\Test\Repository;

use App\Models\Country;
use App\Models\Image;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepository;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;
use TypeError;
use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\ImageEntity;

class WorldHeritageRepository_updateTest extends TestCase
{
    private $repository;
    private $imageCollection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->repository = app(WorldHeritageRepository::class);
        $seeder = new DatabaseSeeder();
        $seeder->run();
        $target = WorldHeritage::query()->where('id', 1418)->first();

        $imgUpdate = new ImageEntity(
            id:        1,
            worldHeritageId: $target->id,
            disk:      $target->images()->first()->disk,
            path:      'old/p1.jpg',
            width:     1200,
            height:    800,
            format:    'jpg',
            checksum:  'oldhash',
            sortOrder: 1,
            alt:       'old alt',
            credit:    'old credit'
        );

        $this->imageCollection = new ImageEntityCollection($imgUpdate);
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
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418',
            'state_parties' => ['ITA'],
            'state_parties_meta' => [
                'ITA' => [
                    'is_primary' => true,
                    'inscription_year' => 2013,
                ]
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
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418',
            'state_parties' => ['FRA', 'ITA'],
            'state_parties_meta' => [
                'FRA' => [
                    'is_primary' => false,
                    'inscription_year' => 5000,
                ],
                'ITA' => [
                    'is_primary' => true,
                    'inscription_year' => 2099,
                ],
            ],
        ];
    }

//    public function test_update_single_ok_check_type(): void
//    {
//        $entity = new WorldHeritageEntity(
//            self::requestSingleContent()['id'],
//            self::requestSingleContent()['official_name'],
//            self::requestSingleContent()['name'],
//            self::requestSingleContent()['country'],
//            self::requestSingleContent()['region'],
//            self::requestSingleContent()['category'],
//            self::requestSingleContent()['year_inscribed'],
//            self::requestSingleContent()['latitude'],
//            self::requestSingleContent()['longitude'],
//            self::requestSingleContent()['is_endangered'],
//            self::requestSingleContent()['name_jp'],
//            self::requestSingleContent()['state_party'],
//            self::requestSingleContent()['criteria'],
//            self::requestSingleContent()['area_hectares'],
//            self::requestSingleContent()['buffer_zone_hectares'],
//            self::requestSingleContent()['short_description'],
//            $this->imageCollection,
//            self::requestSingleContent()['unesco_site_url'],
//            self::requestSingleContent()['state_parties'],
//            self::requestSingleContent()['state_parties_meta']
//        );
//
//        $result = $this->repository->updateOneHeritage($entity);
//
//        $this->assertInstanceOf(WorldHeritageEntity::class, $result);
//    }

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
            $this->imageCollection,
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
            $this->imageCollection,
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
            $this->imageCollection,
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
                'is_primary' => false,
                'inscription_year' => 5000,
            ],
        );
        $this->assertDatabaseMissing(
            'site_state_parties',
            [
                'world_heritage_site_id' => $result->getId(),
                'state_party_code' => 'JPN',
                'is_primary' => true,
                'inscription_year' => 2013,
            ],
        );
    }

    public function test_ng_for_invalid_id(): void
    {
        $id = 9999;
        $this->expectException(TypeError::class);

        $entity = new WorldHeritageEntity(
            $id,
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
            self::requestSingleContent()['unesco_site_url'],
            self::requestSingleContent()['state_parties'],
            self::requestSingleContent()['state_parties_meta']
        );

        $this->repository->updateOneHeritage($entity);
    }

    public function test_update_images_upsert(): void
    {
        $payload = self::requestSingleContent();
        $site    = WorldHeritage::query()->findOrFail($payload['id']);

        $existing = $site->images()->create([
            'disk'       => 's3',
            'path'       => 'old/p1.jpg',
            'width'      => 800,
            'height'     => 600,
            'format'     => 'jpg',
            'checksum'   => 'oldhash',
            'sort_order' => 1,
            'alt'        => 'old alt',
            'credit'     => 'old credit',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $imgUpdate = new ImageEntity(
            id:        $existing->id,
            worldHeritageId: $site->id,
            disk:      's3',
            path:      'new/p1.jpg',
            width:     1200,
            height:    800,
            format:    'jpg',
            checksum:  'newhash',
            sortOrder: 1,
            alt:       'new alt',
            credit:    'new credit'
        );

        $imageCollection = new ImageEntityCollection($imgUpdate);

        $entity = new WorldHeritageEntity(
            $payload['id'],
            $payload['official_name'],
            $payload['name'],
            $payload['country'],
            $payload['region'],
            $payload['category'],
            $payload['year_inscribed'],
            $payload['latitude'],
            $payload['longitude'],
            $payload['is_endangered'],
            $payload['name_jp'],
            $payload['state_party'],
            $payload['criteria'],
            $payload['area_hectares'],
            $payload['buffer_zone_hectares'],
            $payload['short_description'],
            $imageCollection,
            $payload['unesco_site_url'],
            $payload['state_parties'] ?? [],
            $payload['state_parties_meta'] ?? []
        );

        $this->repository->updateOneHeritage($entity);

        $this->assertDatabaseHas('images', [
            'id'                => $existing->id,
            'world_heritage_id' => $site->id,
            'path'              => 'new/p1.jpg',
            'format'            => 'jpg',
            'sort_order'        => 1,
            'alt'               => 'new alt',
            'credit'            => 'new credit',
        ]);

    }
}
