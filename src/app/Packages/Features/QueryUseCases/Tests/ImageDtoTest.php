<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use Tests\TestCase;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;

class ImageDtoTest extends TestCase
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
            'id' => 9,
            'url' => 'http://localhost/storage/seed/world_heritage/1133/img1.jpg',
            'sortOrder' => 1,
            'isPrimary' => true,
        ];
    }

    public function test_image_dto_check_type(): void
    {
        $dto = new ImageDto(
            $this->arrayData()['id'],
            $this->arrayData()['url'],
            $this->arrayData()['sortOrder'],
            $this->arrayData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageDto::class, $dto);
    }

    public function test_image_dto_check_value(): void
    {
        $dto = new ImageDto(
            $this->arrayData()['id'],
            $this->arrayData()['url'],
            $this->arrayData()['sortOrder'],
            $this->arrayData()['isPrimary'],
        );

        $this->assertSame($this->arrayData()['id'], $dto->getId());
        $this->assertSame($this->arrayData()['url'], $dto->getUrl());
        $this->assertSame($this->arrayData()['sortOrder'], $dto->getSortOrder());
        $this->assertSame($this->arrayData()['isPrimary'], $dto->getIsPrimary());
    }
}