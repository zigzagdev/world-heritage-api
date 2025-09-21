<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Packages\Domains\Ports\SignedUrlPort;
use App\Packages\Domains\Ports\ObjectStoragePort;
use App\Packages\Features\QueryUseCases\UseCase\ImageUploadUseCase;
use App\Packages\Domains\ImageEntityCollection;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImageUploadUseCaseTest extends TestCase
{
    private $signedUrl;
    private $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signedUrl = Mockery::mock(SignedUrlPort::class);
        $this->storage   = Mockery::mock(ObjectStoragePort::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_init_generates_signed_urls_and_object_keys(): void
    {
        $unescoId = 'ABC123';
        $items = [
            ['mime' => 'image/jpeg', 'alt' => 'front', 'credit' => 'me'],
            ['mime' => 'image/png', 'sort_order' => 5],
            ['mime' => 'image/jpeg', 'ext' => 'jpg', 'sort_order' => 2],
        ];

        $expected1 = "heritages/{$unescoId}/001.jpg";
        $expected2 = "heritages/{$unescoId}/005.png";
        $expected3 = "heritages/{$unescoId}/002.jpg";

        $this->signedUrl
            ->shouldReceive('forPut')
            ->with('gcs', $expected1, 'image/jpeg', 600)
            ->andReturn('https://signed/1');

        $this->signedUrl
            ->shouldReceive('forPut')
            ->with('gcs', $expected2, 'image/png', 600)
            ->andReturn('https://signed/2');

        $this->signedUrl
            ->shouldReceive('forPut')
            ->with('gcs', $expected3, 'image/jpeg', 600)
            ->andReturn('https://signed/3');

        $useCase = new ImageUploadUseCase($this->signedUrl, $this->storage);
        $out = $useCase->init($unescoId, $items);

        $this->assertCount(3, $out);

        $this->assertSame($expected1, $out[0]['object_key']);
        $this->assertSame('https://signed/1', $out[0]['put_url']);
        $this->assertSame('image/jpeg', $out[0]['content_type']);
        $this->assertSame(1, $out[0]['sort_order']);
        $this->assertSame('front', $out[0]['alt']);
        $this->assertSame('me', $out[0]['credit']);

        $this->assertSame($expected2, $out[1]['object_key']);
        $this->assertSame('https://signed/2', $out[1]['put_url']);
        $this->assertSame('image/png', $out[1]['content_type']);
        $this->assertSame(5, $out[1]['sort_order']);

        $this->assertSame($expected3, $out[2]['object_key']);
        $this->assertSame('https://signed/3', $out[2]['put_url']);
        $this->assertSame('image/jpeg', $out[2]['content_type']);
        $this->assertSame(2, $out[2]['sort_order']);
    }

    public function test_init_rejects_unsupported_mime(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $useCase = new ImageUploadUseCase($this->signedUrl, $this->storage);
        $useCase->init('ABC123', [
            ['mime' => 'image/gif'],
        ]);
    }

    public function test_buildImageCollectionAfterPut_builds_entities_when_objects_exist(): void
    {
        $confirmed = [
            ['object_key' => 'heritages/ABC123/001.jpg', 'sort_order' => 1, 'alt' => 'front', 'credit' => 'me'],
            ['object_key' => 'heritages/ABC123/002.png', 'sort_order' => 2],
        ];

        $this->storage
            ->shouldReceive('exists')
            ->with('gcs', 'heritages/ABC123/001.jpg')
            ->andReturn(true);

        $this->storage
            ->shouldReceive('exists')
            ->with('gcs', 'heritages/ABC123/002.png')
            ->andReturn(true);

        $useCase = new ImageUploadUseCase($this->signedUrl, $this->storage);

        $collection = $useCase->buildImageCollectionAfterPut($confirmed);
        $this->assertInstanceOf(ImageEntityCollection::class, $collection);

        $items = $collection->getItems();
        $this->assertCount(2, $items);

        $img1 = $items[0];
        $path1      = $img1->getPath()      ?? $img1->path      ?? null;
        $disk1      = $img1->getDisk()      ?? $img1->disk      ?? null;
        $format1    = $img1->getFormat()    ?? $img1->format    ?? null;
        $sortOrder1 = $img1->getSortOrder() ?? $img1->sortOrder ?? null;
        $alt1       = $img1->getAlt()       ?? $img1->alt       ?? null;
        $credit1    = $img1->getCredit()    ?? $img1->credit    ?? null;

        $this->assertSame('heritages/ABC123/001.jpg', $path1);
        $this->assertSame('gcs', $disk1);
        $this->assertSame('jpg', $format1);
        $this->assertSame(1, $sortOrder1);
        $this->assertSame('front', $alt1);
        $this->assertSame('me', $credit1);


        $img2 = $items[1];
        $path2      = $img2->getPath()      ?? $img2->path      ?? null;
        $disk2      = $img2->getDisk()      ?? $img2->disk      ?? null;
        $format2    = $img2->getFormat()    ?? $img2->format    ?? null;
        $sortOrder2 = $img2->getSortOrder() ?? $img2->sortOrder ?? null;

        $this->assertSame('heritages/ABC123/002.png', $path2);
        $this->assertSame('gcs', $disk2);
        $this->assertSame('png', $format2);
        $this->assertSame(2, $sortOrder2);
    }

    public function test_buildImageCollectionAfterPut_throws_when_object_missing(): void
    {
        $this->expectException(RuntimeException::class);

        $confirmed = [
            ['object_key' => 'heritages/ABC123/001.jpg', 'sort_order' => 1],
        ];

        $this->storage
            ->shouldReceive('exists')
            ->with('gcs', 'heritages/ABC123/001.jpg')
            ->andReturn(false);

        $useCase = new ImageUploadUseCase($this->signedUrl, $this->storage);
        $useCase->buildImageCollectionAfterPut($confirmed);
    }
}
