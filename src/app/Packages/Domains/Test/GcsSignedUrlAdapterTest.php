<?php

namespace App\Packages\Domains\Test;

use App\Packages\Domains\Adapter\GcsSignedUrlAdapter;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Mockery as m;
use Tests\TestCase;

class GcsSignedUrlAdapterTest extends TestCase
{
    private function setGcsDisk(): void
    {
        Config::set('filesystems.disks.gcs', [
            'driver' => 'gcs',
            'bucket' => 'test-bucket',
            'root'   => 'rootprefix',
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_forGet_returns_url_when_object_exists(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);
        $object  = m::mock(StorageObject::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);
        $bucket->shouldReceive('object')->once()
            ->with('rootprefix/docs/test.pdf')->andReturn($object);

        $object->shouldReceive('exists')->once()->andReturn(true);
        $object->shouldReceive('signedUrl')->once()->withArgs(function ($expiresAt, array $opts) {
            $this->assertSame('v4', $opts['version'] ?? null);
            $this->assertSame('GET', $opts['method'] ?? null);
            $this->assertInstanceOf(\DateTimeInterface::class, $expiresAt);
            return true;
        })->andReturn('https://signed.url/get');

        $adapter = new GcsSignedUrlAdapter($storage);
        $url = $adapter->forGet('gcs', 'docs/test.pdf', 180);

        $this->assertSame('https://signed.url/get', $url);
    }

    public function test_forGet_throws_when_object_not_found(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);
        $object  = m::mock(StorageObject::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);
        $bucket->shouldReceive('object')->once()
            ->with('rootprefix/missing.png')->andReturn($object);

        $object->shouldReceive('exists')->once()->andReturn(false);

        $adapter = new GcsSignedUrlAdapter($storage);

        $this->expectException(InvalidArgumentException::class);
        $adapter->forGet('gcs', 'missing.png', 60);
    }

    public function test_forPut_returns_url_and_sets_content_type(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);
        $object  = m::mock(StorageObject::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);
        $bucket->shouldReceive('object')->once()
            ->with('rootprefix/uploads/img.jpg')->andReturn($object);

        $object->shouldReceive('signedUrl')->once()->withArgs(function ($expiresAt, array $opts) {
            $this->assertSame('v4',  $opts['version'] ?? null);
            $this->assertSame('PUT', $opts['method']  ?? null);
            $this->assertSame('image/jpeg', $opts['contentType'] ?? null);
            $this->assertInstanceOf(\DateTimeInterface::class, $expiresAt);
            return true;
        })->andReturn('https://signed.url/put');

        $adapter = new GcsSignedUrlAdapter($storage);
        $url = $adapter->forPut('gcs', 'uploads/img.jpg', 'image/jpeg', 600);

        $this->assertSame('https://signed.url/put', $url);
    }

    public function test_invalid_disk_configuration_throws_on_put(): void
    {
        Config::set('filesystems.disks.gcs', null);

        $storage = m::mock(StorageClient::class);
        $adapter = new GcsSignedUrlAdapter($storage);

        $this->expectException(\InvalidArgumentException::class);
        $adapter->forPut('gcs', 'x', 'text/plain', 60);
    }
}
