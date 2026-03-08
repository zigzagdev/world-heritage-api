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
        $input = self::arrayDataNoStateParty();
        $dto = WorldHeritageSummaryFactory::build($input);

        $this->assertSame($input['id'], $dto->getId());
        $this->assertSame($input['official_name'], $dto->getOfficialName());
        $this->assertSame($input['name'], $dto->getName());
        $this->assertEmpty($dto->getCountry());
        $this->assertSame($input['region'], $dto->getRegion());
        $this->assertSame($input['category'], $dto->getCategory());
        $this->assertSame($input['year_inscribed'], $dto->getYearInscribed());
        $this->assertNullOrZeroEquivalent($input['latitude'], $dto->getLatitude(), 'latitude');
        $this->assertNullOrZeroEquivalent($input['longitude'], $dto->getLongitude(), 'longitude');
        $this->assertNullOrZeroEquivalent($input['area_hectares'], $dto->getAreaHectares(), 'area_hectares');
        $this->assertNullOrZeroEquivalent($input['buffer_zone_hectares'], $dto->getBufferZoneHectares(), 'buffer_zone_hectares');
        $this->assertSame($input['is_endangered'], $dto->isEndangered());
        $this->assertSame($input['heritage_name_jp'], $dto->getHeritageNameJp());
        $this->assertEmpty($dto->getStateParty());
        $this->assertSame($input['criteria'], $dto->getCriteria());
        $this->assertSame($input['short_description'], $dto->getShortDescription());
        $this->assertSame($input['image_url'], $dto->getImageUrl()->url);
    }

    public function test_build_returns_dto_with_much_country_values(): void
    {
        $input = self::arrayDataTransnational();
        $dto = WorldHeritageSummaryFactory::build($input);

        $this->assertSame($input['id'], $dto->getId());
        $this->assertSame($input['official_name'], $dto->getOfficialName());
        $this->assertSame($input['name'], $dto->getName());
        $this->assertSame($input['region'], $dto->getRegion());
        $this->assertSame($input['category'], $dto->getCategory());
        $this->assertSame($input['year_inscribed'], $dto->getYearInscribed());
        $this->assertNullOrZeroEquivalent($input['latitude'], $dto->getLatitude(), 'latitude');
        $this->assertNullOrZeroEquivalent($input['longitude'], $dto->getLongitude(), 'longitude');
        $this->assertNullOrZeroEquivalent($input['area_hectares'], $dto->getAreaHectares(), 'area_hectares');
        $this->assertNullOrZeroEquivalent($input['buffer_zone_hectares'], $dto->getBufferZoneHectares(), 'buffer_zone_hectares');
        $this->assertSame($input['is_endangered'], $dto->isEndangered());
        $this->assertSame($input['heritage_name_jp'], $dto->getHeritageNameJp());
        $this->assertSame('', $dto->getCountry());

        $this->assertSame($input['criteria'], $dto->getCriteria());
        $this->assertSame($input['short_description'], $dto->getShortDescription());
        $this->assertSame($input['state_parties_meta'], $dto->getStatePartiesMeta());
    }

    private function assertNullOrZeroEquivalent(
        float|int|string|null $expected,
        float|int|string|null $actual,
        string $fieldName
    ): void {
        $e = $this->normalizeNullableFloat($expected);
        $a = $this->normalizeNullableFloat($actual);

        if ($e === null && $a === null) {
            $this->assertTrue(true);
            return;
        }

        if ($e === null && $a === 0.0) {
            $this->assertTrue(true, "{$fieldName}: expected null, got 0.0 (allowed)");
            return;
        }

        if ($e === 0.0 && $a === null) {
            $this->assertTrue(true, "{$fieldName}: expected 0.0, got null (allowed)");
            return;
        }

        // それ以外は厳密に一致（float比較はデルタ不要なはずの値なのでそのまま）
        $this->assertSame($e, $a, "{$fieldName}: expected {$e}, got {$a}");
    }

    private function normalizeNullableFloat(float|int|string|null $v): ?float
    {
        if ($v === null) return null;

        // 数値文字列も許容（DB/配列が文字列で来るケース対策）
        if (is_string($v)) {
            $s = trim($v);
            if ($s === '') return null;
            if (!is_numeric($s)) return null;
            return (float) $s;
        }

        return (float) $v;
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
            'heritage_name_jp' => 'エルサレムの旧市街とその城壁群',
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
            'heritage_name_jp' => 'カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林',
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