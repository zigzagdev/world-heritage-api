<?php

namespace App\Packages\Features\QueryUseCases\Tests\Dto;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDtoCollectionFactory;
use Database\Seeders\CountrySeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorldHeritageDtoCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        (new CountrySeeder())->run();
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
                'state_parties' => ['ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'],
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

    public function test_collection_check_type(): void
    {
        $data = self::arrayData();
        $dtoCollection = WorldHeritageDtoCollectionFactory::build($data);

        $this->assertInstanceOf(WorldHeritageDtoCollection::class, $dtoCollection);
    }

    public function test_collection_check_value_without_thumbnail(): void
    {
        $data = self::arrayData();
        $dtoCollection = WorldHeritageDtoCollectionFactory::build($data);

        $expectFirstCode = [
            0 => "ALB",
            1 => "AUT",
            2 => "BEL",
            3 => "BIH",
            4 => "BGR",
            5 => "HRV",
            6 => "CZE",
            7 => "FRA",
            8 => "DEU",
            9 => "ITA",
            10 => "MKD",
            11 => "POL",
            12 => "ROU",
            13 => "SVK",
            14 => "SVN",
            15 => "ESP",
            16 => "CHE",
            17 => "UKR",
        ];

        $expectSecondCode = [
            0 => "CHN",
            1 => "KAZ",
            2 => "KGZ",
        ];

        foreach ($dtoCollection->getHeritages() as $index => $dto) {
            $eachData = self::arrayData()[$index];

            $this->assertSame($eachData['id'], $dto->getId());
            $this->assertSame($eachData['official_name'], $dto->getOfficialName());
            $this->assertSame($eachData['name'], $dto->getName());
            $this->assertSame($eachData['country'], $dto->getCountry());
            $this->assertSame($eachData['region'], $dto->getRegion());
            $this->assertSame($eachData['category'], $dto->getCategory());
            $this->assertSame($eachData['year_inscribed'], $dto->getYearInscribed());
            $this->assertSame($eachData['area_hectares'], $dto->getAreaHectares());
            $this->assertSame($eachData['buffer_zone_hectares'], $dto->getBufferZoneHectares());
            $this->assertSame($eachData['is_endangered'], $dto->isEndangered());
            $this->assertSame($eachData['latitude'], $dto->getLatitude());
            $this->assertSame($eachData['longitude'], $dto->getLongitude());
            $this->assertSame($eachData['short_description'], $dto->getShortDescription());
            $this->assertSame($eachData['unesco_site_url'], $dto->getUnescoSiteUrl());

            $this->assertEqualsCanonicalizing($eachData['criteria'] ?? [], $dto->getCriteria() ?? []);

            $expectedCodes = $eachData['state_party_codes'] ?? $eachData['state_parties'] ?? [];
            $this->assertEqualsCanonicalizing($expectedCodes, $dto->getStatePartyCodes());

            $this->assertEquals($eachData['state_parties_meta'] ?? [], $dto->getStatePartiesMeta());
        }
    }

    public function test_summary_array_matches_expected_with_thumbnail(): void
    {
        $data = self::arrayData();
        $dtoCollection = WorldHeritageDtoCollectionFactory::build($data);

        $summary = $dtoCollection->toSummaryArray();

        $this->assertCount(count($data), $summary);

        collect($summary)->map(function ($item) {
            $this->assertArrayNotHasKey('images', $item);
            $this->assertArrayNotHasKey('imageUrl', $item);
            $this->assertArrayNotHasKey('state_parties', $item);
            $this->assertArrayHasKey('thumbnail', $item);
            $this->assertTrue(is_string($item['thumbnail']) || is_null($item['thumbnail']));

            return collect($item)->keyBy(fn($v,$k)=>Str::snake($k))->toArray();
        })->toArray();
    }
}
