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

    private static function arrayNoIdData(): array
    {
        return [
            'id' => null,
            'url' => 'public/images/sample.jpg',
            'sortOrder' => 0,
            'isPrimary' => true,
        ];
    }

    private static function arrayData(): array
    {
        return [
            'id' => 1,
            'url' => 'public/images/sample.jpg',
            'sortOrder' => 0,
            'isPrimary' => true,
        ];
    }

    public function test_entity_check_type_with_no_id_entity(): void
    {
        $entity = new ImageEntity(
            self::arrayNoIdData()['id'],
            self::arrayNoIdData()['url'],
            self::arrayNoIdData()['sortOrder'],
            self::arrayNoIdData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageEntity::class, $entity);
    }

    public function test_entity_check_type_with_id_entity(): void
    {
        $entity = new ImageEntity(
            self::arrayData()['id'],
            self::arrayData()['url'],
            self::arrayData()['sortOrder'],
            self::arrayData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageEntity::class, $entity);
    }

    public function test_entity_check_value(): void
    {
        $entity = new ImageEntity(
            id: null,
            url: self::arrayData()['url'],
            sortOrder: self::arrayData()['sortOrder'],
            isPrimary: self::arrayData()['isPrimary'],
        );

        $this->assertSame(null, $entity->getId());
        $this->assertSame(self::arrayData()['url'], $entity->getUrl());
        $this->assertSame(self::arrayData()['sortOrder'], $entity->getSortOrder());
        $this->assertSame(self::arrayData()['isPrimary'], $entity->getIsPrimary());
    }

    public function test_entity_check_value_with_no_id(): void
    {
        $entity = new ImageEntity(
            id: self::arrayNoIdData()['id'],
            url: self::arrayNoIdData()['url'],
            sortOrder: self::arrayNoIdData()['sortOrder'],
            isPrimary: self::arrayNoIdData()['isPrimary'],
        );

        $this->assertSame(null, $entity->getId());
        $this->assertSame(self::arrayNoIdData()['url'], $entity->getUrl());
        $this->assertSame(self::arrayNoIdData()['sortOrder'], $entity->getSortOrder());
        $this->assertSame(self::arrayNoIdData()['isPrimary'], $entity->getIsPrimary());
    }
}