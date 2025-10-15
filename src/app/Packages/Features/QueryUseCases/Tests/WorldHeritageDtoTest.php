<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Database\Seeders\CountrySeeder;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class WorldHeritageDtoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->seed(CountrySeeder::class);
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

    private static function arraySingleData(): array
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
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'state_parties' => ['JP'],
            'state_parties_meta' => [
                'JP' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
            'images' => [
                [
                    'id' => null,
                    'world_heritage_id' => 668,
                    'disk' => 'gcs',
                    'path' => 'wh/1133/photo.jpg',
                    'width' => null,
                    'height' => null,
                    'format' => 'jpg',
                    'checksum' => null,
                    'sort_order' => 1,
                    'alt' => null,
                    'credit' => null,
                ],
            ],
        ];
    }

    private static function arrayMultiData(): array
    {
        return [
            'id' => 1133,
            'official_name' => 'Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe',
            'name' => 'Ancient and Primeval Beech Forests',
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
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
            'state_parties' => [
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
            ],
            'images' => [
                [
                    'id' => null,
                    'world_heritage_id' => 1133,
                    'disk' => 'gcs',
                    'path' => 'wh/1133/photo.jpg',
                    'width' => null,
                    'height' => null,
                    'format' => 'jpg',
                    'checksum' => null,
                    'sort_order' => 1,
                    'alt' => null,
                    'credit' => null,
                ],
                [
                    'id' => null,
                    'world_heritage_id' => 1133,
                    'disk' => 'gcs',
                    'path' => 'wh/1133/photo2.jpg',
                    'width' => null,
                    'height' => null,
                    'format' => 'jpg',
                    'checksum' => null,
                    'sort_order' => 2,
                    'alt' => null,
                    'credit' => null,
                ],
            ],
        ];
    }

    private function createImageEntityCollectionFrom(array $images): ImageDtoCollection
    {
        $collection = new ImageDtoCollection();
        foreach ($images as $img) {
            $collection->add(new ImageDto(
                $img['id'],
                'http://localhost/storage/' . ltrim($img['path'], '/'),
                (int) $img['sort_order'],
                $img['width'],
                $img['height'],
                $img['format'],
                $img['alt'],
                $img['credit'],
                ((int) $img['sort_order']) === 1,
                $img['checksum'],
            ));
        }
        return $collection;
    }

    public function test_dto_check_single_type(): void
    {
        $data = $this->arraySingleData();
        $images = $this->createImageEntityCollectionFrom($data['images']);

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['official_name'],
            $data['name'],
            $data['country'],
            $data['region'],
            $data['category'],
            $data['year_inscribed'],
            $data['latitude'],
            $data['longitude'],
            $data['is_endangered'],
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $images,
            $data['unesco_site_url'] ?? null
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $dto);
    }

    public function test_dto_check_single_value(): void
    {
        $data = $this->arraySingleData();
        $images = $this->createImageEntityCollectionFrom($data['images']);

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['official_name'],
            $data['name'],
            $data['country'],
            $data['region'],
            $data['category'],
            $data['year_inscribed'],
            $data['latitude'],
            $data['longitude'],
            $data['is_endangered'],
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $images,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );

        $this->assertSame($data['id'], $dto->getId());
        $this->assertSame($data['official_name'], $dto->getOfficialName());
        $this->assertSame($data['name'], $dto->getName());
        $this->assertSame($data['country'], $dto->getCountry());
        $this->assertSame($data['region'], $dto->getRegion());
        $this->assertSame($data['category'], $dto->getCategory());
        $this->assertSame($data['year_inscribed'], $dto->getYearInscribed());
        $this->assertSame($data['latitude'], $dto->getLatitude());
        $this->assertSame($data['longitude'], $dto->getLongitude());
        $this->assertSame($data['is_endangered'], $dto->isEndangered());
        $this->assertSame($data['name_jp'], $dto->getNameJp());
        $this->assertSame($data['state_party'], $dto->getStateParty());
        $this->assertSame($data['criteria'], $dto->getCriteria());
        $this->assertSame($data['area_hectares'], $dto->getAreaHectares());
        $this->assertSame($data['buffer_zone_hectares'], $dto->getBufferZoneHectares());
        $this->assertSame($data['short_description'], $dto->getShortDescription());
        $this->assertSame($data['unesco_site_url'], $dto->getUnescoSiteUrl());
        $this->assertSame($data['state_parties'], $dto->getStatePartyCodes());
        $this->assertSame($data['state_parties_meta'], $dto->getStatePartiesMeta());

        $actualImages = $dto->getImages();
        $expectedImages = array_map(function ($img) {
            return [
                'id'         => $img['id'],
                'url'        => 'http://localhost/storage/' . ltrim($img['path'], '/'),
                'sort_order' => (int) $img['sort_order'],
                'width'      => $img['width'],
                'height'     => $img['height'],
                'format'     => $img['format'],
                'alt'        => $img['alt'],
                'credit'     => $img['credit'],
                'is_primary' => ((int) $img['sort_order']) === 1,
                'checksum'   => $img['checksum'],
            ];
        }, $data['images']);

        $this->assertSame($expectedImages, $actualImages);
    }

    public function test_dto_check_multi_type(): void
    {
        $data = $this->arrayMultiData();
        $images = $this->createImageEntityCollectionFrom($data['images']);

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['official_name'],
            $data['name'],
            $data['country'],
            $data['region'],
            $data['category'],
            $data['year_inscribed'],
            $data['latitude'],
            $data['longitude'],
            $data['is_endangered'],
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $images,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $dto);
    }

    public function test_dto_check_multi_value(): void
    {
        $data = $this->arrayMultiData();
        $images = $this->createImageEntityCollectionFrom($data['images']);

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['official_name'],
            $data['name'],
            $data['country'],
            $data['region'],
            $data['category'],
            $data['year_inscribed'],
            $data['latitude'],
            $data['longitude'],
            $data['is_endangered'],
            $data['name_jp'] ?? null,
            $data['state_party'] ?? null,
            $data['criteria'] ?? null,
            $data['area_hectares'] ?? null,
            $data['buffer_zone_hectares'] ?? null,
            $data['short_description'] ?? null,
            $images,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );

        $this->assertSame($data['id'], $dto->getId());
        $this->assertSame($data['official_name'], $dto->getOfficialName());
        $this->assertSame($data['name'], $dto->getName());
        $this->assertSame($data['country'], $dto->getCountry());
        $this->assertSame($data['region'], $dto->getRegion());
        $this->assertSame($data['category'], $dto->getCategory());
        $this->assertSame($data['year_inscribed'], $dto->getYearInscribed());
        $this->assertSame($data['latitude'], $dto->getLatitude());
        $this->assertSame($data['longitude'], $dto->getLongitude());
        $this->assertSame($data['is_endangered'], $dto->isEndangered());
        $this->assertSame($data['name_jp'], $dto->getNameJp());
        $this->assertSame($data['state_party'], $dto->getStateParty());
        $this->assertSame($data['criteria'], $dto->getCriteria());
        $this->assertSame($data['area_hectares'], $dto->getAreaHectares());
        $this->assertSame($data['buffer_zone_hectares'], $dto->getBufferZoneHectares());
        $this->assertSame($data['short_description'], $dto->getShortDescription());
        $this->assertSame($data['unesco_site_url'], $dto->getUnescoSiteUrl());
        $this->assertSame($data['state_parties'], $dto->getStatePartyCodes());
        $this->assertSame($data['state_parties_meta'], $dto->getStatePartiesMeta());

        $actualImages = $dto->getImages();
        $expectedImages = array_map(function ($img) {
            return [
                'id' => $img['id'],
                'url' => 'http://localhost/storage/' . ltrim($img['path'], '/'),
                'sort_order' => (int) $img['sort_order'],
                'width' => $img['width'],
                'height' => $img['height'],
                'format' => $img['format'],
                'alt' => $img['alt'],
                'credit' => $img['credit'],
                'is_primary' => ((int) $img['sort_order']) === 1,
                'checksum' => $img['checksum'],
            ];
        }, $data['images']);

        $this->assertSame($expectedImages, $actualImages);
    }
}
