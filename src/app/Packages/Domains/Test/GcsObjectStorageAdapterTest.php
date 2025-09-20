<?php

namespace App\Packages\Domains\Test;

use App\Packages\Domains\Adapter\GcsObjectStorageAdapter;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Illuminate\Support\Facades\Config;
use Mockery as m;
use Tests\TestCase;

class GcsObjectStorageAdapterTest extends TestCase
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

    public function test_put_with_string_payload_calls_upload_with_expected_options(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);

        $storage->shouldReceive('bucket')->once()
            ->with('test-bucket')->andReturn($bucket);

        $bucket->shouldReceive('upload')->once()->withArgs(function ($payload, $options) {
            $this->assertSame('hello', $payload);
            $this->assertSame('rootprefix/path/to/test.txt', $options['name']);
            $this->assertSame('private', $options['predefinedAcl']);
            $this->assertSame('text/plain', $options['metadata']['contentType']);
            return true;
        });

        $adapter = new GcsObjectStorageAdapter($storage);
        $adapter->put('gcs', 'path/to/test.txt', 'text/plain', 'hello');
        $this->addToAssertionCount(1);
    }

    public function test_put_with_resource_payload_uploads_stream(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);

        $bucket->shouldReceive('upload')->once()->withArgs(function ($payload, $options) {
            $this->assertIsResource($payload);
            $this->assertSame('rootprefix/imgs/a.png', $options['name']);
            $this->assertSame('image/png', $options['metadata']['contentType']);
            return true;
        });

        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, 'PNGDATA');
        rewind($stream);

        $adapter = new GcsObjectStorageAdapter($storage);
        $adapter->put('gcs', 'imgs/a.png', 'image/png', $stream);
        fclose($stream);
        $this->addToAssertionCount(1);
    }

    public function test_put_with_unsupported_payload_throws(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);

        $storage->shouldReceive('bucket')->andReturn($bucket);

        $this->expectException(\InvalidArgumentException::class);

        $adapter = new GcsObjectStorageAdapter($storage);
        $adapter->put('gcs', 'x', 'text/plain', new \stdClass());
    }

    public function test_delete_swallows_not_found(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);
        $object  = m::mock(StorageObject::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);
        $bucket->shouldReceive('object')->once()
            ->with('rootprefix/path/to/missing.txt')->andReturn($object);

        $object->shouldReceive('delete')->once()->andThrow(new NotFoundException('not found'));

        $adapter = new GcsObjectStorageAdapter($storage);

        $adapter->delete('gcs', 'path/to/missing.txt');
        $this->addToAssertionCount(1);
    }

    public function test_exists_delegates_to_object_exists(): void
    {
        $this->setGcsDisk();

        $storage = m::mock(StorageClient::class);
        $bucket  = m::mock(Bucket::class);
        $object  = m::mock(StorageObject::class);

        $storage->shouldReceive('bucket')->once()->with('test-bucket')->andReturn($bucket);
        $bucket->shouldReceive('object')->once()
            ->with('rootprefix/test/test.jpg')->andReturn($object);

        $object->shouldReceive('exists')->once()->andReturn(true);

        $adapter = new GcsObjectStorageAdapter($storage);
        $this->assertTrue($adapter->exists('gcs', 'test/test.jpg'));
    }

    public function test_invalid_disk_configuration_throws(): void
    {
        Config::set('filesystems.disks.gcs', null);

        $storage = m::mock(StorageClient::class);
        $adapter = new GcsObjectStorageAdapter($storage);

        $this->expectException(\InvalidArgumentException::class);
        $adapter->exists('gcs', 'any');
    }
}
