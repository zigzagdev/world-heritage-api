<?php

namespace App\Packages\Features\QueryUseCases\Tests\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDetailFactory;
use Tests\TestCase;

class WorldHeritageDtoDetailFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林",
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
                'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA',
                'MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR',
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
                    'id' => 10,
                    'url' => 'https://cdn.example.com/a.jpg',
                    'sort_order' => 1,
                    'is_primary' => true,
                    'width' => 1600,
                    'height' => 900,
                    'format' => 'jpeg',
                    'alt' => 'a',
                    'credit' => 'photog A',
                    'checksum' => 'aaa',
                ],
                [
                    'id' => 11,
                    'url' => 'https://cdn.example.com/b.jpg',
                    'sort_order' => 5,
                    'width' => 1200,
                    'height' => 800,
                    'format' => 'jpeg',
                    'alt' => 'b',
                    'credit' => 'photog B',
                    'is_primary' => false,
                    'checksum' => 'bbb',
                ],
            ],
        ];
    }

    public function test_check_return_data_type(): void
    {
        $result = WorldHeritageDetailFactory::build(self::arrayData());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_check_return_data_value(): void
    {
        $result = WorldHeritageDetailFactory::build(self::arrayData());
        $input  = self::arrayData();

        $this->assertSame($input['id'], $result->getId());
        $this->assertSame($input['official_name'], $result->getOfficialName());
        $this->assertSame($input['name'], $result->getName());
        $this->assertSame($input['name_jp'], $result->getNameJp());
        $this->assertSame($input['country'], $result->getCountry());
        $this->assertSame($input['region'], $result->getRegion());
        $this->assertSame($input['category'], $result->getCategory());
        $this->assertSame($input['year_inscribed'], $result->getYearInscribed());
        $this->assertSame($input['state_party'], $result->getStateParty());
        $this->assertSame($input['criteria'], $result->getCriteria());
        $this->assertSame($input['area_hectares'], $result->getAreaHectares());
        $this->assertSame($input['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertSame($input['is_endangered'], $result->isEndangered());
        $this->assertSame($input['latitude'], $result->getLatitude());
        $this->assertSame($input['longitude'], $result->getLongitude());
        $this->assertSame($input['short_description'], $result->getShortDescription());
        $this->assertSame($input['unesco_site_url'], $result->getUnescoSiteUrl());

        $this->assertSame(
            $input['state_party_codes'],
            $result->getStatePartyCodes()
        );

        $this->assertSame(
            $input['state_parties_meta'],
            $result->getStatePartiesMeta()
        );

        $images = $result->getImages();
        $this->assertCount(2, $images);

        $this->assertSame($input['images'][0]['id'],         $images[0]['id']);
        $this->assertSame($input['images'][0]['url'],        $images[0]['url']);
        $this->assertSame($input['images'][0]['sort_order'], $images[0]['sort_order']);
        $this->assertSame($input['images'][0]['width'],      $images[0]['width']);
        $this->assertSame($input['images'][0]['height'],     $images[0]['height']);
        $this->assertSame($input['images'][0]['format'],     $images[0]['format']);
        $this->assertSame($input['images'][0]['alt'],        $images[0]['alt']);
        $this->assertSame($input['images'][0]['credit'],     $images[0]['credit']);
        $this->assertSame($input['images'][0]['is_primary'], $images[0]['is_primary']);
        $this->assertSame($input['images'][0]['checksum'],   $images[0]['checksum']);

        $this->assertSame($input['images'][1]['id'],         $images[1]['id']);
        $this->assertSame($input['images'][1]['url'],        $images[1]['url']);
        $this->assertSame($input['images'][1]['sort_order'], $images[1]['sort_order']);
        $this->assertSame($input['images'][1]['width'],      $images[1]['width']);
        $this->assertSame($input['images'][1]['height'],     $images[1]['height']);
        $this->assertSame($input['images'][1]['format'],     $images[1]['format']);
        $this->assertSame($input['images'][1]['alt'],        $images[1]['alt']);
        $this->assertSame($input['images'][1]['credit'],     $images[1]['credit']);
        $this->assertSame($input['images'][1]['is_primary'], $images[1]['is_primary']);
        $this->assertSame($input['images'][1]['checksum'],   $images[1]['checksum']);
    }
}
