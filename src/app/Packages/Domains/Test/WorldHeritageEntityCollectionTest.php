<?php

namespace App\Packages\Domains\Test;

use App\Packages\Domains\WorldHeritageEntityCollection;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageEntity;

class WorldHeritageEntityCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function arrayData(): array
    {
        return [
            [
                'id' => 1,
                'unesco_id' => '668',
                'official_name' => 'Historic Monuments of Ancient Nara',
                'name' => 'Historic Monuments of Ancient Nara',
                'name_jp' => '古都奈良の文化財',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1998,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6851,
                'longitude' => 135.8048,
                'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            ],
            [
                'id' => 2,
                'unesco_id' => '1234',
                'official_name' => 'Example Heritage Site',
                'name' => 'Example Heritage Site',
                'name_jp' => '例の文化遺産',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['vii', 'viii'],
                'year_inscribed' => 2000,
                'area_hectares' => 500.0,
                'buffer_zone_hectares' => 400.0,
                'is_endangered' => true,
                'latitude' => 35.6895,
                'longitude' => 139.6917,
                'short_description' => 'An example of a natural heritage site.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/1234/',
            ],
            [
                'id' => 3,
                'unesco_id' => '669',
                'official_name' => 'Shrines and Temples of Nikko',
                'name' => 'Shrines and Temples of Nikko',
                'name_jp' => '日光の社寺',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1999,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 36.7578,
                'longitude' => 139.598,
                'short_description' => 'Lavishly decorated shrines set among ancient cedar trees.',
                'image_url' => '',
                'unesco_site_url' => 'https://whc.unesco.org/en/list/669/',
            ],
        ];
    }

    public function test_collection_check_type(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    $data['id'],
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['is_endangered'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null
                );
            }, $this->arrayData())
        );

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $collection);
    }

    public function test_collection_check_empty_value(): void
    {
        $collection = new WorldHeritageEntityCollection();
        $this->assertSame([], $collection->getAllHeritages());
    }

    public function test_collection_check_count_value(): void
    {
        $collection = new WorldHeritageEntityCollection(
            array_map(function ($data) {
                return new WorldHeritageEntity(
                    $data['id'],
                    $data['unesco_id'],
                    $data['official_name'],
                    $data['name'],
                    $data['country'],
                    $data['region'],
                    $data['category'],
                    $data['year_inscribed'],
                    $data['is_endangered'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['name_jp'] ?? null,
                    $data['state_party'] ?? null,
                    $data['criteria'] ?? null,
                    $data['area_hectares'] ?? null,
                    $data['buffer_zone_hectares'] ?? null,
                    $data['short_description'] ?? null,
                    $data['image_url'] ?? null,
                    $data['unesco_site_url'] ?? null
                );
            }, $this->arrayData())
        );

        $this->assertCount(3, $collection->getAllHeritages());
    }
}