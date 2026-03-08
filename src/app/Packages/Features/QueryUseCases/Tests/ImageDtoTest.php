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

    private static function arrayData(): array
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
            self::arrayData()['id'],
            self::arrayData()['url'],
            self::arrayData()['sortOrder'],
            self::arrayData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageDto::class, $dto);
    }

    public function test_image_dto_check_value(): void
    {
        $dto = new ImageDto(
            self::arrayData()['id'],
            self::arrayData()['url'],
            self::arrayData()['sortOrder'],
            self::arrayData()['isPrimary'],
        );

        $this->assertSame(self::arrayData()['id'], $dto->getId());
        $this->assertSame(self::arrayData()['url'], $dto->getUrl());
        $this->assertSame(self::arrayData()['sortOrder'], $dto->getSortOrder());
        $this->assertSame(self::arrayData()['isPrimary'], $dto->getIsPrimary());
    }
}