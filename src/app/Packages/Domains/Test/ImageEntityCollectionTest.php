<?php

namespace App\Packages\Domains\Test;

use Tests\TestCase;
use App\Packages\Domains\ImageEntity;
use App\Packages\Domains\ImageEntityCollection;

class ImageEntityCollectionTest extends TestCase
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
            [
                'disk' => 'public',
                'path' => 'images/sample1.jpg',
                'width' => 800,
                'height' => 600,
                'format' => 'jpg',
                'checksum'  => str_repeat('a', 64),
                'sortOrder' => 0,
                'alt' => 'Sample Image 1',
                'credit' => 'John Doe',
            ],
            [
                'disk' => 'public',
                'path' => 'images/sample2.png',
                'width' => 1024,
                'height' => 768,
                'format' => 'png',
                'checksum'  => str_repeat('b', 64),
                'sortOrder' => 1,
                'alt' => 'Sample Image 2',
                'credit' => 'Jane Smith',
            ]
        ];
    }

    public function test_check_collection_test_type(): void
    {
        $collection = new ImageEntityCollection(
            array_map(function ($data) {
                return new ImageEntity(
                    id: null,
                    disk: $data['disk'],
                    path: $data['path'],
                    width: $data['width'],
                    height: $data['height'],
                    format: $data['format'],
                    checksum: $data['checksum'],
                    sortOrder: $data['sortOrder'],
                    alt: $data['alt'],
                    credit: $data['credit'],
                );
            }, self::arrayData())
        );

        $this->assertInstanceOf(ImageEntityCollection::class, $collection);
    }

    public function test_check_collection_test_value(): void
    {
        $collection = new ImageEntityCollection(
            array_map(function ($data) {
                return new ImageEntity(
                    id: null,
                    disk: $data['disk'],
                    path: $data['path'],
                    width: $data['width'],
                    height: $data['height'],
                    format: $data['format'],
                    checksum: $data['checksum'],
                    sortOrder: $data['sortOrder'],
                    alt: $data['alt'],
                    credit: $data['credit'],
                );
            }, self::arrayData())
        );

        foreach ($collection->getItems() as $key => $item) {
            $this->assertInstanceOf(ImageEntity::class, $item);
            $this->assertEquals(self::arrayData()[$key]['disk'], $item->getDisk());
            $this->assertEquals(self::arrayData()[$key]['path'], $item->getPath());
            $this->assertEquals(self::arrayData()[$key]['width'], $item->getWidth());
            $this->assertEquals(self::arrayData()[$key]['height'], $item->getHeight());
            $this->assertEquals(self::arrayData()[$key]['format'], $item->getFormat());
            $this->assertEquals(self::arrayData()[$key]['checksum'], $item->getChecksum());
            $this->assertEquals(self::arrayData()[$key]['sortOrder'], $item->getSortOrder());
            $this->assertEquals(self::arrayData()[$key]['alt'], $item->getAlt());
            $this->assertEquals(self::arrayData()[$key]['credit'], $item->getCredit());
        }
    }
}