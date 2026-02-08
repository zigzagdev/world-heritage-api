<?php

namespace App\Packages\Features\QueryUseCases\Tests\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageSummaryFactory;
use Tests\TestCase;

class WorldHeritageDtoSummaryFactoryTest extends TestCase
{
    public function test_build_returns_dto_no_country_value(): void
    {
        $result = WorldHeritageSummaryFactory::build(self::arrayDataNoStateParty());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_build_returns_dto_with_much_countries(): void
    {
        $result = WorldHeritageSummaryFactory::build(self::arrayDataTransnational());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    public function test_build_returns_dto_with_no_country_values(): void
    {
        $dto = WorldHeritageSummaryFactory::build(self::arrayDataNoStateParty());

        $this->assertSame($dto->getId(), self::arrayDataNoStateParty()['id']);
        $this->assertSame($dto->getOfficialName(), self::arrayDataNoStateParty()['official_name']);
        $this->assertSame($dto->getName(), self::arrayDataNoStateParty()['name']);
        $this->assertNull($dto->getCountry());
        $this->assertSame($dto->getRegion(), self::arrayDataNoStateParty()['region']);
        $this->assertSame($dto->getCategory(), self::arrayDataNoStateParty()['category']);
        $this->assertSame($dto->getYearInscribed(), self::arrayDataNoStateParty()['year_inscribed']);
        $this->assertSame($dto->getLatitude(), self::arrayDataNoStateParty()['latitude']);
        $this->assertSame($dto->getLongitude(), self::arrayDataNoStateParty()['longitude']);
        $this->assertSame($dto->isEndangered(), self::arrayDataNoStateParty()['is_endangered']);
        $this->assertSame($dto->getNameJp(), self::arrayDataNoStateParty()['name_jp']);
        $this->assertNull($dto->getStateParty());
        $this->assertSame($dto->getCriteria(), self::arrayDataNoStateParty()['criteria']);
        $this->assertSame($dto->getAreaHectares(), self::arrayDataNoStateParty()['area_hectares']);
        $this->assertSame($dto->getBufferZoneHectares(), self::arrayDataNoStateParty()['buffer_zone_hectares']);
        $this->assertSame($dto->getShortDescription(), self::arrayDataNoStateParty()['short_description']);
        $this->assertSame($dto->getImageUrl()->url, self::arrayDataNoStateParty()['image_url']);
    }

    public function test_build_returns_dto_with_much_country_values()
    {
        $dto = WorldHeritageSummaryFactory::build(self::arrayDataTransnational());

        $this->assertSame($dto->getId(), self::arrayDataTransnational()['id']);
        $this->assertSame($dto->getOfficialName(), self::arrayDataTransnational()['official_name']);
        $this->assertSame($dto->getName(), self::arrayDataTransnational()['name']);
        $this->assertSame($dto->getRegion(), self::arrayDataTransnational()['region']);
        $this->assertSame($dto->getCategory(), self::arrayDataTransnational()['category']);
        $this->assertSame($dto->getYearInscribed(), self::arrayDataTransnational()['year_inscribed']);
        $this->assertSame($dto->getLatitude(), self::arrayDataTransnational()['latitude']);
        $this->assertSame($dto->getLongitude(), self::arrayDataTransnational()['longitude']);
        $this->assertSame($dto->isEndangered(), self::arrayDataTransnational()['is_endangered']);
        $this->assertSame($dto->getNameJp(), self::arrayDataTransnational()['name_jp']);
        $this->assertNull($dto->getStateParty());
        $this->assertSame($dto->getCriteria(), self::arrayDataTransnational()['criteria']);
        $this->assertSame($dto->getAreaHectares(), self::arrayDataTransnational()['area_hectares']);
        $this->assertSame($dto->getBufferZoneHectares(), self::arrayDataTransnational()['buffer_zone_hectares']);
        $this->assertSame($dto->getShortDescription(), self::arrayDataTransnational()['short_description']);
        $this->assertSame($dto->getStatePartiesMeta(), self::arrayDataTransnational()['state_parties_meta']);
    }

    public static function provideSummaryFactoryCases(): array
    {
        return [
            'no_state_party_jerusalem' => [
                'input' => self::arrayDataTransnational(),
                'expects' => [
                    'id' => 148,
                    'country' => null,
                    'state_party' => null,
                    'state_party_codes' => [],
                    'state_parties_meta' => [],
                ],
            ],
            'transnational_beech_forests' => [
                'input' => self::arrayDataTransnational(),
                'expects' => [
                    'id' => 1133,
                    'country' => null,
                    'state_party' => null,
                    'state_party_codes' => [
                        'ALB',
                        'AUT',
                        'BEL',
                        'BGR',
                        'BIH',
                        'CHE',
                        'CZE',
                        'DEU',
                        'ESP',
                        'FRA',
                        'HRV',
                        'ITA',
                        'MKD',
                        'POL',
                        'ROU',
                        'SVK',
                        'SVN',
                        'UKR',
                    ],
                    'state_parties_meta' => self::expectedTransnationalMeta(),
                ],
            ],
        ];
    }

    private static function expectedTransnationalMeta(): array
    {
        $codes = [
            'ALB',
            'AUT',
            'BEL',
            'BGR',
            'BIH',
            'CHE',
            'CZE',
            'DEU',
            'ESP',
            'FRA',
            'HRV',
            'ITA',
            'MKD',
            'POL',
            'ROU',
            'SVK',
            'SVN',
            'UKR',
        ];

        $meta = [];
        foreach ($codes as $code) {
            $meta[$code] = [
                'is_primary' => $code === 'MKD',
            ];
        }
        return $meta;
    }

    private static function arrayDataNoStateParty(): array
    {
        return [
            'id' => 148,
            'official_name' => 'Old City of Jerusalem and its Walls',
            'name' => 'Old City of Jerusalem and its Walls',
            'name_jp' => 'エルサレムの旧市街とその城壁群',
            'country' => null,
            'region' => 'ARB',
            'category' => 'Cultural',
            'criteria' => ['ii', 'iii'],
            'state_party' => null,
            'state_party_code' => null,
            'year_inscribed' => 1981,
            'area_hectares' => 0.0,
            'buffer_zone_hectares' => null,
            'is_endangered' => true,
            'latitude' => 31.7777778,
            'longitude' => 35.2316667,
            'short_description' => "As a holy city for Judaism, Christianity and Islam, Jerusalem has always been of great symbolic importance. Among its 220 historic monuments, the Dome of the Rock stands out: built in the 7th century, it is decorated with beautiful geometric and floral motifs. It is recognized by all three religions as the site of Abraham's sacrifice. The Wailing Wall delimits the quarters of the different religious communities, while the Resurrection rotunda in the Church of the Holy Sepulchre houses Christ's tomb.",
            'thumbnail_id' => null,
            'unesco_site_url' => null,
            'state_parties' => [],
            'state_parties_meta' => [],
            'image_url' => 'https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/files/b50ed866599cc5d7dacb0fbb621d4bb5',
        ];
    }

    private static function arrayDataTransnational(): array
    {
        $codes = [
            'ALB',
            'AUT',
            'BEL',
            'BGR',
            'BIH',
            'CHE',
            'CZE',
            'DEU',
            'ESP',
            'FRA',
            'HRV',
            'ITA',
            'MKD',
            'POL',
            'ROU',
            'SVK',
            'SVN',
            'UKR',
        ];

        $meta = [];
        foreach ($codes as $code) {
            $meta[$code] = ['is_primary' => $code === 'MKD'];
        }

        return [
            'id' => 1133,
            'official_name' => 'Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe',
            'name' => 'Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe',
            'name_jp' => 'カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林',
            'country' => null,
            'region' => 'EUR',
            'category' => 'Natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'state_party_code' => $codes,
            'year_inscribed' => 2007,
            'area_hectares' => 99947.81,
            'buffer_zone_hectares' => null,
            'is_endangered' => false,
            'latitude' => 48.9,
            'longitude' => 22.1833333,
            'short_description' => 'This transnational property includes 93 component parts in 18 countries. Since the end of the last Ice Age, European Beech spread from a few isolated refuge areas in the Alps, Carpathians, Dinarides, Mediterranean and Pyrenees over a short period of a few thousand years in a process that is still ongoing. The successful expansion across a whole continent is related to the tree’s adaptability and tolerance of different climatic, geographical and physical conditions.',
            'thumbnail_id' => null,
            'unesco_site_url' => null,
            'state_parties' => $codes,
            'state_parties_meta' => $meta,
            'image_url' => 'https://data.unesco.org/api/explore/v2.1/catalog/datasets/whc001/files/3a042d6a324c301d604f2f478f35c09f',
        ];
    }
}
