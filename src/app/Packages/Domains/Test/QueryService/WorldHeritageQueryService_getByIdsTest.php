<?php

namespace App\Packages\Domains\Test\QueryService;

use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Models\WorldHeritage;

class WorldHeritageQueryService_getByIdsTest extends TestCase
{

    private $queryService;
    private int $currentPage;
    private int $perPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryService = app(WorldHeritageQueryService::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayData(): array
    {
        return [
            [
                'id' => 1,
                'unesco_id' => '660',
                'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name_jp' => '法隆寺地域の仏教建造物',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6147,
                'longitude' => 135.7355,
                'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
            ],
            [
                'id' => 2,
                'unesco_id' => '661',
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'name_jp' => '姫路城',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "A masterpiece of Japanese castle architecture in original form.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661/',
            ],
            [
                'id' => 3,
                'unesco_id' => '662',
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'name_jp' => '屋久島',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 30.3581,
                'longitude' => 130.546,
                'short_description' => "A subtropical island with ancient cedar forests and diverse ecosystems.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662/',
            ],
            [
                'id' => 4,
                'unesco_id' => '663',
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'name_jp' => '白神山地',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 40.5167,
                'longitude' => 140.05,
                'short_description' => "Pristine beech forest with minimal human impact.",
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663/',
            ]
        ];
    }

    public function test_getByIds_count_objects(): void
    {
        $ids = [1, 2];
        $result = $this->queryService->getHeritagesByIds($ids, 1, 10);

        $this->assertCount(2, $result->getCollection());
    }

    public function test_getByIds_check_each_value(): void
    {
        $ids = [1, 2];
        $result = $this->queryService->getHeritagesByIds($ids, 1, 10);

        $expectedMap = collect(self::arrayData())
            ->filter(fn($row) => in_array($row['id'], $ids))
            ->keyBy('id');

        foreach ($result->getCollection() as $entity) {
            $id = $entity['id'];
            $expected = $expectedMap[$id] ?? null;
            $this->assertSame($expected['id'], $entity['id']);
            $this->assertEquals($expected['unesco_id'], $entity['unesco_id']);
            $this->assertSame($expected['official_name'], $entity['official_name']);
            $this->assertSame($expected['name'], $entity['name']);
            $this->assertSame($expected['name_jp'], $entity['name_jp']);
            $this->assertSame($expected['country'], $entity['country']);
            $this->assertSame($expected['region'], $entity['region']);
            $this->assertSame($expected['state_party'], $entity['state_party']);
            $this->assertSame($expected['category'], $entity['category']);
            $this->assertSame($expected['criteria'], $entity['criteria']);
            $this->assertEquals($expected['year_inscribed'], $entity['year_inscribed']);
            $this->assertSame($expected['area_hectares'], $entity['area_hectares']);
            $this->assertSame($expected['buffer_zone_hectares'], $entity['buffer_zone_hectares']);
            $this->assertIsBool($expected['is_endangered'], $entity['is_endangered']);
            $this->assertIsFloat($expected['latitude'], $entity['latitude']);
            $this->assertIsFloat($expected['longitude'], $entity['longitude']);
            $this->assertSame($expected['short_description'], $entity['short_description']);
            $this->assertSame($expected['image_url'], $entity['image_url']);
            $this->assertSame($expected['unesco_site_url'], $entity['unesco_site_url']);
        }
    }
}