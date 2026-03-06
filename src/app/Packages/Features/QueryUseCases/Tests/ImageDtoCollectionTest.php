<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;

class ImageDtoCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function arrayMultiData(): array
    {
        return [
            [
                'id' => 9,
                'url' => 'http://localhost/storage/seed/world_heritage/1133/img1.jpg',
                'sortOrder' => 2,
                'isPrimary' => false,
            ],
            [
                'id' => 10,
                'url' => 'http://localhost/storage/seed/world_heritage/1133/img2.jpg',
                'sortOrder' => 1,
                'isPrimary' => true,
            ], [
                'id' => 11,
                'url' => 'http://localhost/storage/seed/world_heritage/1133/img3.jpg',
                'sortOrder' => 3,
                'isPrimary' => false,
            ]
        ];
    }

    public function test_image_dto_collection_check_type(): void
    {
        $collection = new ImageDtoCollection();

        foreach (self::arrayMultiData() as $data) {
            $dto = new ImageDto(
                $data['id'],
                $data['url'],
                $data['sortOrder'],
                $data['isPrimary'],
            );
            $collection->add($dto);
        }

        $this->assertInstanceOf(ImageDtoCollection::class, $collection);
    }

    public function test_check_image_collection_value(): void
    {
        $collection = new ImageDtoCollection();

        foreach (self::arrayMultiData() as $data) {
            $dto = new ImageDto(
                $data['id'],
                $data['url'],
                $data['sortOrder'],
                $data['isPrimary'],
            );
            $collection->add($dto);
        }

        $this->assertCount(3, $collection->toArray());

        foreach ($collection->toArray() as $index => $item) {
            $this->assertEquals(self::arrayMultiData()[$index]['id'], $item['id']);
            $this->assertEquals(self::arrayMultiData()[$index]['url'], $item['url']);
            $this->assertEquals(self::arrayMultiData()[$index]['sortOrder'], $item['sort_order']);
            $this->assertEquals(self::arrayMultiData()[$index]['isPrimary'], $item['is_primary']);
        }
    }
}