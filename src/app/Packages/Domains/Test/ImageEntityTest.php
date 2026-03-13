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

    private function arrayNoIdData(): array
    {
        return [
            'id' => null,
            'url' => 'public/images/sample.jpg',
            'sortOrder' => 0,
            'isPrimary' => true,
        ];
    }

    private function arrayData(): array
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
            $this->arrayNoIdData()['id'],
            $this->arrayNoIdData()['url'],
            $this->arrayNoIdData()['sortOrder'],
            $this->arrayNoIdData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageEntity::class, $entity);
    }

    public function test_entity_check_type_with_id_entity(): void
    {
        $entity = new ImageEntity(
            $this->arrayData()['id'],
            $this->arrayData()['url'],
            $this->arrayData()['sortOrder'],
            $this->arrayData()['isPrimary'],
        );

        $this->assertInstanceOf(ImageEntity::class, $entity);
    }

    public function test_entity_check_value(): void
    {
        $entity = new ImageEntity(
            id: null,
            url: $this->arrayData()['url'],
            sortOrder: $this->arrayData()['sortOrder'],
            isPrimary: $this->arrayData()['isPrimary'],
        );

        $this->assertSame(null, $entity->getId());
        $this->assertSame($this->arrayData()['url'], $entity->getUrl());
        $this->assertSame($this->arrayData()['sortOrder'], $entity->getSortOrder());
        $this->assertSame($this->arrayData()['isPrimary'], $entity->getIsPrimary());
    }

    public function test_entity_check_value_with_no_id(): void
    {
        $entity = new ImageEntity(
            id: $this->arrayNoIdData()['id'],
            url: $this->arrayNoIdData()['url'],
            sortOrder: $this->arrayNoIdData()['sortOrder'],
            isPrimary: $this->arrayNoIdData()['isPrimary'],
        );

        $this->assertSame(null, $entity->getId());
        $this->assertSame($this->arrayNoIdData()['url'], $entity->getUrl());
        $this->assertSame($this->arrayNoIdData()['sortOrder'], $entity->getSortOrder());
        $this->assertSame($this->arrayNoIdData()['isPrimary'], $entity->getIsPrimary());
    }
}