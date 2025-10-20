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
                'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD',
                'POL','ROU','SVK','SVN','ESP','CHE','UKR'
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

        foreach ($result->toArray() as $key => $value) {
            $this->assertEquals(self::arrayData()[$key], $value);
        }
    }
}