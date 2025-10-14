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
                'width' => 1200,
                'height' => 800,
                'format' => 'jpg',
                'alt' => 'Ancient and Primeval Beech Forests #1',
                'credit' => 'seed',
                'isPrimary' => false,
                'checksum' => 'abc123',
            ],
            [
                'id' => 10,
                'url' => 'http://localhost/storage/seed/world_heritage/1133/img2.jpg',
                'sortOrder' => 1,
                'width' => 1200,
                'height' => 800,
                'format' => 'jpg',
                'alt' => 'Ancient and Primeval Beech Forests #2',
                'credit' => 'seed',
                'isPrimary' => true,
                'checksum' => 'def456',
            ], [
                'id' => 11,
                'url' => 'http://localhost/storage/seed/world_heritage/1133/img3.jpg',
                'sortOrder' => 3,
                'width' => 1200,
                'height' => 800,
                'format' => 'jpg',
                'alt' => 'Ancient and Primeval Beech Forests #3',
                'credit' => 'seed',
                'isPrimary' => false,
                'checksum' => 'ghi789',
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
                $data['width'],
                $data['height'],
                $data['format'],
                $data['alt'],
                $data['credit'],
                $data['isPrimary'],
                $data['checksum'],
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
                $data['width'],
                $data['height'],
                $data['format'],
                $data['alt'],
                $data['credit'],
                $data['isPrimary'],
                $data['checksum'],
            );
            $collection->add($dto);
        }

        $this->assertCount(3, $collection->toArray());

        foreach ($collection->toArray() as $index => $item) {
            $this->assertEquals(self::arrayMultiData()[$index]['id'], $item['id']);
            $this->assertEquals(self::arrayMultiData()[$index]['url'], $item['url']);
            $this->assertEquals(self::arrayMultiData()[$index]['sortOrder'], $item['sort_order']);
            $this->assertEquals(self::arrayMultiData()[$index]['width'], $item['width']);
            $this->assertEquals(self::arrayMultiData()[$index]['height'], $item['height']);
            $this->assertEquals(self::arrayMultiData()[$index]['format'], $item['format']);
            $this->assertEquals(self::arrayMultiData()[$index]['alt'], $item['alt']);
            $this->assertEquals(self::arrayMultiData()[$index]['credit'], $item['credit']);
            $this->assertEquals(self::arrayMultiData()[$index]['isPrimary'], $item['is_primary']);
            $this->assertEquals(self::arrayMultiData()[$index]['checksum'], $item['checksum']);
        }
    }
}