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

    private function arrayMultiData(): array
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

        foreach ($this->arrayMultiData() as $data) {
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

        foreach ($this->arrayMultiData() as $data) {
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
            $this->assertEquals($this->arrayMultiData()[$index]['id'], $item['id']);
            $this->assertEquals($this->arrayMultiData()[$index]['url'], $item['url']);
            $this->assertEquals($this->arrayMultiData()[$index]['sortOrder'], $item['sort_order']);
            $this->assertEquals($this->arrayMultiData()[$index]['isPrimary'], $item['is_primary']);
        }
    }
}