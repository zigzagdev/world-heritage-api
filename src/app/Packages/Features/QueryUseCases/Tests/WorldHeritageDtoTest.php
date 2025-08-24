<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Models\WorldHeritage;
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
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arraySingleData(): array
    {
        return [
            'id' => 1,
            'unesco_id' => '668',
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
            'state_parties' => ['JP'],
            'state_parties_meta' => [
                'JP' => [
                    'is_primary' => true,
                    'inscription_year' => 1998,
                ],
            ],
        ];
    }

    private static function arrayMultiData(): array
    {
        return [
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
        ];
    }

    public function test_dto_check_single_type(): void
    {
        $data = $this->arraySingleData();

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['unesco_id'],
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
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $dto);
    }

    public function test_dto_check_single_value(): void
    {
        $data = $this->arraySingleData();

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['unesco_id'],
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
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );


        $this->assertSame($data['id'], $dto->getId());
        $this->assertSame($data['unesco_id'], $dto->getUnescoId());
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
        $this->assertSame($data['image_url'], $dto->getImageUrl());
        $this->assertSame($data['unesco_site_url'], $dto->getUnescoSiteUrl());
        $this->assertSame($data['state_parties'], $dto->getStatePartyCodes());
        $this->assertSame($data['state_parties_meta'], $dto->getStatePartiesMeta());
    }

    public function test_dto_check_multi_type(): void
    {
        $data = $this->arrayMultiData();

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['unesco_id'],
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
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );

        $this->assertInstanceOf(WorldHeritageDto::class, $dto);
    }

    public function test_dto_check_multi_value(): void
    {
        $data = $this->arrayMultiData();

        $dto = new WorldHeritageDto(
            $data['id'],
            $data['unesco_id'],
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
            $data['image_url'] ?? null,
            $data['unesco_site_url'] ?? null,
            $data['state_parties'] ?? [],
            $data['state_parties_meta'] ?? []
        );

        $this->assertSame($data['unesco_id'], $dto->getUnescoId());
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
        $this->assertSame($data['image_url'], $dto->getImageUrl());
        $this->assertSame($data['unesco_site_url'], $dto->getUnescoSiteUrl());
        $this->assertSame($data['state_parties'], $dto->getStatePartyCodes());
        $this->assertSame($data['state_parties_meta'], $dto->getStatePartiesMeta());
    }
}