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
                'id' => 1,
                'url' => 'public/images/sample.jpg',
                'sortOrder' => 0,
                'isPrimary' => true,
            ],
            [
                'id' => 2,
                'url' => 'public/images/sample2.jpg',
                'sortOrder' => 1,
                'isPrimary' => false,
            ]
        ];
    }

    public function test_check_collection_test_type(): void
    {
        $collection = new ImageEntityCollection();

        foreach (self::arrayData() as $data) {
            $collection->add(new ImageEntity(
                id: $data['id'],
                url: $data['url'],
                sortOrder: $data['sortOrder'],
                isPrimary: $data['isPrimary'],
            ));
        }

        $this->assertInstanceOf(ImageEntityCollection::class, $collection);
    }

    public function test_check_collection_test_value(): void
    {
        $collection = new ImageEntityCollection();

        foreach (self::arrayData() as $data) {
            $collection->add(new ImageEntity(
                id: $data['id'],
                url: $data['url'],
                sortOrder: $data['sortOrder'],
                isPrimary: $data['isPrimary'],
            ));
        }
        foreach ($collection->getItems() as $key => $item) {
            $this->assertInstanceOf(ImageEntity::class, $item);
            $this->assertSame(self::arrayData()[$key]['id'], $item->getId());
            $this->assertSame(self::arrayData()[$key]['url'], $item->getUrl());
            $this->assertEquals(self::arrayData()[$key]['sortOrder'], $item->getSortOrder());
            $this->assertSame(self::arrayData()[$key]['isPrimary'], $item->getIsPrimary());
        }
    }
}