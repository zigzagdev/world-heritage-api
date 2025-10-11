<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\ImageEntity;
use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\CreateWorldHeritageUseCase;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\UseCase\ImageUploadUseCase;

class CreateWorldHeritageUseCaseTest extends TestCase
{
    private $imageUpload;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
        $seeder->run();
        $this->imageUpload = Mockery::mock(ImageUploadUseCase::class);
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
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function mockImage(): array
    {
        return [
            'id' => null,
            'world_heritage_id' => null,
            'disk' => 'gcs',
            'path' => 'heritages/1133/001.jpg',
            'width' => null,
            'height' => null,
            'format' => 'jpg',
            'checksum' => null,
            'sort_order' => 1,
            'alt' => 'front',
            'credit' => 'me',
        ];
    }

    private function mockImageEntity(): ImageEntityCollection
    {
        $entity = new ImageEntity(
            $this->mockImage()['id'],
            $this->mockImage()['world_heritage_id'],
            $this->mockImage()['disk'],
            $this->mockImage()['path'],
            $this->mockImage()['width'],
            $this->mockImage()['height'],
            $this->mockImage()['format'],
            $this->mockImage()['checksum'],
            $this->mockImage()['sort_order'],
            $this->mockImage()['alt'],
            $this->mockImage()['credit']
        );

        return new ImageEntityCollection($entity);
    }

    private function mockImageUploadUseCase(): ImageUploadUseCase
    {
        $useCase = Mockery::mock(ImageUploadUseCase::class);

        $useCase->shouldReceive('buildImageCollectionAfterPut')
            ->with(self::arrayData()['images_confirmed'])
            ->andReturn($this->mockImageEntity());

        return $useCase;
    }

    private function arrayData(): array
    {
        return [
            'id' => 668,
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'state_party' => null,
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => ['JPN'],
            'state_parties_meta' => [
                'JPN' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
            'images_confirmed' => [
                [
                    'object_key' => 'heritages/668/001.jpg',
                    'sort_order' => 1,
                    'alt'        => 'Todai-ji Great Buddha Hall',
                    'credit'     => 'Photo by XXX',
                ],
            ],
        ];
    }

    private static function noImageData(): array
    {
        return [
            'id' => 668,
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => '古都奈良の文化財',
            'country' => 'Japan',
            'region' => 'Asia',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'state_party' => null,
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => ['JPN'],
            'state_parties_meta' => [
                'JPN' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
            'images_confirmed' => [

            ]
        ];
    }

    private function mockRepository(): WorldHeritageRepositoryInterface
    {
        $mock = Mockery::mock(WorldHeritageRepositoryInterface::class);

        $mock
            ->shouldReceive('insertHeritage')
            ->with(Mockery::type(WorldHeritageEntity::class))
            ->andReturn(
                new WorldHeritageEntity(
                    id: $this->arrayData()['id'],
                    officialName: $this->arrayData()['official_name'],
                    name: $this->arrayData()['name'],
                    country: $this->arrayData()['country'],
                    region: $this->arrayData()['region'],
                    category: $this->arrayData()['category'],
                    yearInscribed: $this->arrayData()['year_inscribed'],
                    latitude: $this->arrayData()['latitude'],
                    longitude: $this->arrayData()['longitude'],
                    isEndangered: $this->arrayData()['is_endangered'],
                    nameJp: $this->arrayData()['name_jp'],
                    stateParty: $this->arrayData()['state_party'],
                    criteria: $this->arrayData()['criteria'],
                    areaHectares: $this->arrayData()['area_hectares'],
                    bufferZoneHectares: $this->arrayData()['buffer_zone_hectares'],
                    shortDescription: $this->arrayData()['short_description'],
                    collection: $this->mockImageEntity(),
                    unescoSiteUrl: $this->arrayData()['unesco_site_url'],
                    statePartyCodes: $this->arrayData()['state_parties'],
                    statePartyMeta: $this->arrayData()['state_parties_meta'] ?? [],
                )
            );

        return $mock;
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle($this->arrayData());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle($this->arrayData());

        $this->assertEquals($this->arrayData()['id'], $result->getId());
        $this->assertEquals($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals($this->arrayData()['name'], $result->getName());
        $this->assertEquals($this->arrayData()['country'], $result->getCountry());
        $this->assertEquals($this->arrayData()['region'], $result->getRegion());
        $this->assertEquals($this->arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals($this->arrayData()['category'], $result->getCategory());
        $this->assertEquals($this->arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals($this->arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals($this->arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals($this->arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals($this->arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals($this->arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals($this->arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals($this->arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals($this->arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals($this->arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals($this->arrayData()['state_parties'], $result->getStatePartyCodes());
        $this->assertEquals($this->arrayData()['state_parties_meta'], $result->getStatePartiesMeta());

        $this->assertArrayHasKey('images', $result->toArray());

        $this->assertSame($this->mockImage()['path'], $result->toArray()['images'][0]['path']);
        $this->assertSame($this->mockImage()['disk'], $result->toArray()['images'][0]['disk']);
        $this->assertSame($this->mockImage()['format'], $result->toArray()['images'][0]['format']);
    }

    public function test_use_case_with_out_images(): void
    {
        $useCase = new CreateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle(self::noImageData());

        $this->assertEquals(self::noImageData()['id'], $result->getId());
        $this->assertEquals(self::noImageData()['official_name'], $result->getOfficialName());
        $this->assertEquals(self::noImageData()['name'], $result->getName());
        $this->assertEquals(self::noImageData()['country'], $result->getCountry());
        $this->assertEquals(self::noImageData()['region'], $result->getRegion());
        $this->assertEquals(self::noImageData()['state_party'], $result->getStateParty());
        $this->assertEquals(self::noImageData()['category'], $result->getCategory());
        $this->assertEquals(self::noImageData()['criteria'], $result->getCriteria());
        $this->assertEquals(self::noImageData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals(self::noImageData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals(self::noImageData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals(self::noImageData()['is_endangered'], $result->isEndangered());
        $this->assertEquals(self::noImageData()['latitude'], $result->getLatitude());
        $this->assertEquals(self::noImageData()['longitude'], $result->getLongitude());
        $this->assertEquals(self::noImageData()['name_jp'], $result->getNameJp());
        $this->assertEquals(self::noImageData()['short_description'], $result->getShortDescription());
        $this->assertEquals(self::noImageData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals(self::noImageData()['state_parties'], $result->getStatePartyCodes());
        $this->assertEquals(self::noImageData()['state_parties_meta'], $result->getStatePartiesMeta());
    }
}