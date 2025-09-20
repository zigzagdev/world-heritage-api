<?php

namespace App\Packages\Domains\Adapter;

use App\Packages\Domains\Ports\SignedUrlPort;
use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;
use DateTimeImmutable;

class GcsSignedUrlAdapter implements SignedUrlPort
{
    public function __construct(
        private readonly StorageClient $storage
    ) {}

    public function forGet(string $disk, string $key, int $ttlSec = 300): string
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);
        $object = $bucket->object($objectPath);

        if (!$object->exists()) {
            throw new InvalidArgumentException("Object not found: {$disk}/{$objectPath}");
        }

        $url = $object->signedUrl(
            (new DateTimeImmutable())->modify("+{$ttlSec} seconds"),
            ['version' => 'v4', 'method' => 'GET']
        );

        return $url;
    }

    public function forPut(string $disk, string $key, string $mime, int $ttlSec = 300): string
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);
        $object = $bucket->object($objectPath);

        return $object->signedUrl(
            (new \DateTimeImmutable())->modify("+{$ttlSec} seconds"),
            [
                'version'     => 'v4',
                'method'      => 'PUT',
                'contentType' => $mime,
            ]
        );
    }

    private function resolve(string $disk, string $key): array
    {
        $cfg = config("filesystems.disks.{$disk}");
        if (!$cfg || ($cfg['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException("Disk '{$disk}' is not configured for GCS.");
        }
        $bucket = $this->storage->bucket($cfg['bucket']);
        $prefix = rtrim($cfg['root'] ?? '', '/');
        $objectPath = ltrim(($prefix ? "{$prefix}/" : '').ltrim($key, '/'), '/');
        return [$bucket, $objectPath];
    }
}
