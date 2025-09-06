<?php

namespace App\Packages\Domains\Test\Repository;

use Database\Seeders\CountrySeeder;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageRepository;
use App\Models\Country;

class WorldHeritageRepository_insertManyTest extends TestCase
{

    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new CountrySeeder();
        $seeder->run();
        $this->repository =  app(WorldHeritageRepository::class);
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
                'state_parties' => [
                    'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
                ],
                'state_parties_meta' => [
                    'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
                    'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
                    'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
                    'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
                    'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
                    'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
                    'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
                    'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
                    'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
                    'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
                    'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
                    'POL' => ['is_primary' => false, 'inscription_year' => 2021],
                    'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
                    'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                    'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
                    'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
                    'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
                    'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
                ]
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
    public function test_check_return_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($d) {
                return new WorldHeritageEntity(
                    $d['id'],
                    $d['official_name'],
                    $d['name'],
                    $d['country'],
                    $d['region'],
                    $d['category'],
                    (int) $d['year_inscribed'],
                    isset($d['latitude']) ? (float) $d['latitude'] : null,
                    isset($d['longitude']) ? (float) $d['longitude'] : null,
                    (bool) ($d['is_endangered'] ?? false),
                    $d['name_jp'] ?? null,
                    $d['state_party'] ?? null,
                    is_string($d['criteria'] ?? null)
                        ? json_decode($d['criteria'], true, 512, JSON_THROW_ON_ERROR)
                        : ($d['criteria'] ?? []),
                    isset($d['area_hectares']) ? (float) $d['area_hectares'] : null,
                    isset($d['buffer_zone_hectares']) ? (float) $d['buffer_zone_hectares'] : null,
                    $d['short_description'] ?? null,
                    $d['image_url'] ?? null,
                    $d['unesco_site_url'] ?? null,
                    $d['state_parties'] ?? [],
                    $d['state_parties_meta'] ?? []
                );
            }, self::arrayData())
        );

        $result = $this->repository->insertHeritages($collection);
        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $result);
    }

    public function test_check_return_value(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
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
                    $data['name_jp'],
                    $data['state_party'],
                    $data['criteria'],
                    $data['area_hectares'],
                    $data['buffer_zone_hectares'],
                    $data['short_description'],
                    $data['image_url'],
                    $data['unesco_site_url'],
                    $data['state_parties'] ?? [],
                    $data['state_parties_meta'] ?? []
                );
            }, self::arrayData())
        );

        $result = $this->repository->insertHeritages($collection);


        $expectedFirstCodes = [
            'ALB','AUT','BEL','BGR','BIH','CHE','CZE','DEU','ESP','FRA',
            'HRV','ITA','MKD','POL','ROU','SVK','SVN','UKR',
        ];
        $expectedFirst = [
            'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
            'ALB' => ['is_primary'=>false,'inscription_year'=>2017],
            'AUT' => ['is_primary'=>false,'inscription_year'=>2017],
            'BEL' => ['is_primary'=>false,'inscription_year'=>2017],
            'BIH' => ['is_primary'=>false,'inscription_year'=>2021],
            'BGR' => ['is_primary'=>false,'inscription_year'=>2017],
            'HRV' => ['is_primary'=>false,'inscription_year'=>2017],
            'CZE' => ['is_primary'=>false,'inscription_year'=>2021],
            'FRA' => ['is_primary'=>false,'inscription_year'=>2021],
            'DEU' => ['is_primary'=>false,'inscription_year'=>2011],
            'ITA' => ['is_primary'=>false,'inscription_year'=>2017],
            'MKD' => ['is_primary'=>false,'inscription_year'=>2021],
            'POL' => ['is_primary'=>false,'inscription_year'=>2021],
            'ROU' => ['is_primary'=>false,'inscription_year'=>2017],
            'SVK' => ['is_primary'=>true,'inscription_year'=>2007],
            'SVN' => ['is_primary'=>false,'inscription_year'=>2017],
            'ESP' => ['is_primary'=>false,'inscription_year'=>2017],
            'CHE' => ['is_primary'=>false,'inscription_year'=>2021],
            'UKR' => ['is_primary'=>false,'inscription_year'=>2007],
        ];
        $orderedExpectedFirst = [];
        foreach ($expectedFirstCodes as $code) {
            $orderedExpectedFirst[$code] = $expectedFirst[$code];
        }

        $this->assertSame(
            $expectedFirstCodes,
            $result->getAllHeritages()[0]->getStatePartyCodes()
        );
        $this->assertSame(
            $orderedExpectedFirst,
            $result->getAllHeritages()[0]->getStatePartyMeta()
        );

        $expectedSecondCodes = ['CHN','KAZ','KGZ'];
        $expectedSecond = [
            'CHN' => ['is_primary' => true,  'inscription_year' => 2014],
            'KAZ' => ['is_primary' => false, 'inscription_year' => 2014],
            'KGZ' => ['is_primary' => false, 'inscription_year' => 2014],
        ];
        $orderedExpectedSecond = [];
        foreach ($expectedSecondCodes as $code) {
            $orderedExpectedSecond[$code] = $expectedSecond[$code];
        }

        $this->assertSame(
            $expectedSecondCodes,
            $result->getAllHeritages()[1]->getStatePartyCodes()
        );
        $this->assertSame(
            $orderedExpectedSecond,
            $result->getAllHeritages()[1]->getStatePartyMeta()
        );

        foreach ($result->getAllHeritages() as $key => $value) {
            $this->assertEquals(self::arrayData()[$key]['id'], $value->getid());
            $this->assertEquals(self::arrayData()[$key]['official_name'], $value->getOfficialName());
            $this->assertEquals(self::arrayData()[$key]['name'], $value->getName());
            $this->assertEquals(self::arrayData()[$key]['name_jp'], $value->getNameJp());
            $this->assertEquals(self::arrayData()[$key]['country'], $value->getCountry());
            $this->assertEquals(self::arrayData()[$key]['region'], $value->getRegion());
            $this->assertEquals(self::arrayData()[$key]['category'], $value->getCategory());
            $this->assertEquals(self::arrayData()[$key]['criteria'], $value->getCriteria());
            $this->assertEquals(self::arrayData()[$key]['year_inscribed'], $value->getYearInscribed());
            $this->assertEquals(self::arrayData()[$key]['area_hectares'], $value->getAreaHectares());
            $this->assertEquals(self::arrayData()[$key]['buffer_zone_hectares'], $value->getBufferZoneHectares());
            $this->assertEquals(self::arrayData()[$key]['is_endangered'], $value->isEndangered());
            $this->assertEquals(self::arrayData()[$key]['latitude'], $value->getLatitude());
            $this->assertEquals(self::arrayData()[$key]['longitude'], $value->getLongitude());
            $this->assertEquals(self::arrayData()[$key]['short_description'], $value->getShortDescription());
            $this->assertEquals(self::arrayData()[$key]['image_url'], $value->getImageUrl());
            $this->assertEquals(self::arrayData()[$key]['unesco_site_url'], $value->getUnescoSiteUrl());
        }
    }
}