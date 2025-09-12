<?php

namespace App\Packages\Domains\Test\Repository;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageEntityCollection;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use App\Models\Country;

class WorldHeritageRepository_updateManyTest extends TestCase
{
    private $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->repository = app(WorldHeritageRepositoryInterface::class);
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

    private static function requestData(): array
    {
        return [
            [
                'id' => 1133,
                'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
                'name' => "Ancient and Primeval Beech Forests",
                'name_jp' => '更新した！！！！',
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
                'name_jp' => 'こいつも！！！！',
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
                'state_parties' => ['FRA'],
                'state_parties_meta' => [
                    'FRA' => ['is_primary' => true, 'inscription_year' => 2020]
                ],
            ],
        ];
    }

    public function test_repository_check_type(): void
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
            }, self::requestData())
        );

        $result = $this->repository->updateManyHeritages($collection);

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $result);
    }

    public function test_repository_check_value(): void
    {
        $beforeFirst = WorldHeritage::find(1133);
        $beforeName = $beforeFirst->name_jp;

        $beforeSecond = WorldHeritage::find(1442);
        $beforeName2PartyCode = $beforeSecond->first()->state_party_code;
        $beforeName2Meta = $beforeSecond->first()->state_parties_meta;

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
            }, self::requestData())
        );

        $result = $this->repository->updateManyHeritages($collection);

        foreach ($result->getAllHeritages() as $key => $value) {
            $this->assertEquals(self::requestData()[$key]['id'], $value->getid());
            $this->assertEquals(self::requestData()[$key]['official_name'], $value->getOfficialName());
            $this->assertEquals(self::requestData()[$key]['name'], $value->getName());
            $this->assertEquals(self::requestData()[$key]['name_jp'], $value->getNameJp());
            $this->assertEquals(self::requestData()[$key]['country'], $value->getCountry());
            $this->assertEquals(self::requestData()[$key]['region'], $value->getRegion());
            $this->assertEquals(self::requestData()[$key]['category'], $value->getCategory());
            $this->assertEquals(self::requestData()[$key]['criteria'], $value->getCriteria());
            $this->assertEquals(self::requestData()[$key]['year_inscribed'], $value->getYearInscribed());
            $this->assertEquals(self::requestData()[$key]['area_hectares'], $value->getAreaHectares());
            $this->assertEquals(self::requestData()[$key]['buffer_zone_hectares'], $value->getBufferZoneHectares());
            $this->assertEquals(self::requestData()[$key]['is_endangered'], $value->isEndangered());
            $this->assertEquals(self::requestData()[$key]['latitude'], $value->getLatitude());
            $this->assertEquals(self::requestData()[$key]['longitude'], $value->getLongitude());
            $this->assertEquals(self::requestData()[$key]['short_description'], $value->getShortDescription());
            $this->assertEquals(self::requestData()[$key]['image_url'], $value->getImageUrl());
            $this->assertEquals(self::requestData()[$key]['unesco_site_url'], $value->getUnescoSiteUrl());
        }

        $this->assertNotSame($beforeName, $result->getAllHeritages()[0]->getNameJp());
        $this->assertNotSame(
            $beforeName2PartyCode,
            $result->getAllHeritages()[1]->getStatePartyCodes()[0]
        );
        $this->assertNotSame(
            $beforeName2Meta,
            $result->getAllHeritages()[1]->getStatePartyMeta()['FRA']
        );
    }
}