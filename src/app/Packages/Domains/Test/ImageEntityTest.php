<?php

namespace App\Packages\Domains\Test;

use App\Packages\Domains\ImageEntity;
use Tests\TestCase;

class ImageEntityTest extends TestCase
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
            'disk' => 'public',
            'path' => 'images/sample.jpg',
            'width' => 800,
            'height' => 600,
            'format' => 'jpg',
            'checksum'  => str_repeat('a', 64),
            'sortOrder' => 0,
            'alt' => 'Sample Image',
            'credit' => 'John Doe',
        ];
    }

    public function test_entity_check_type(): void
    {
        $entity = new ImageEntity(
            id: null,
            disk: self::arrayData()['disk'],
            path: self::arrayData()['path'],
            width: self::arrayData()['width'],
            height: self::arrayData()['height'],
            format: self::arrayData()['format'],
            checksum: self::arrayData()['checksum'],
            sortOrder: self::arrayData()['sortOrder'],
            alt: self::arrayData()['alt'],
            credit: self::arrayData()['credit'],
        );

        $this->assertInstanceOf(ImageEntity::class, $entity);
    }

    public function test_entity_check_value(): void
    {
        $entity = new ImageEntity(
            id: null,
            disk: self::arrayData()['disk'],
            path: self::arrayData()['path'],
            width: self::arrayData()['width'],
            height: self::arrayData()['height'],
            format: self::arrayData()['format'],
            checksum: self::arrayData()['checksum'],
            sortOrder: self::arrayData()['sortOrder'],
            alt: self::arrayData()['alt'],
            credit: self::arrayData()['credit'],
        );

        $this->assertSame(null, $entity->getId());
        $this->assertSame(self::arrayData()['disk'], $entity->getDisk());
        $this->assertSame(self::arrayData()['path'], $entity->getPath());
        $this->assertSame(self::arrayData()['width'], $entity->getWidth());
        $this->assertSame(self::arrayData()['height'], $entity->getHeight());
        $this->assertSame(self::arrayData()['format'], $entity->getFormat());
        $this->assertSame(self::arrayData()['checksum'], $entity->getChecksum());
        $this->assertSame(self::arrayData()['sortOrder'], $entity->getSortOrder());
        $this->assertSame(self::arrayData()['alt'], $entity->getAlt());
        $this->assertSame(self::arrayData()['credit'], $entity->getCredit());
    }
}