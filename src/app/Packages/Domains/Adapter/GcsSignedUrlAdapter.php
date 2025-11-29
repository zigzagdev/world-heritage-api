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
        $cfg = config("filesystems.disks.{$disk}");

        if (! $cfg) {
            throw new InvalidArgumentException("Disk '{$disk}' is not defined.");
        }

        $driver = $cfg['driver'] ?? null;

        if ($driver === 'gcs') {
            [$bucket, $objectPath] = $this->resolveGcs($cfg, $key);
            $object = $bucket->object($objectPath);

            if (! $object->exists()) {
                throw new InvalidArgumentException("Object not found: {$disk}/{$objectPath}");
            }

            return $object->signedUrl(
                (new DateTimeImmutable())->modify("+{$ttlSec} seconds"),
                ['version' => 'v4', 'method' => 'GET']
            );
        }

        if ($driver === 'local') {
            return url('storage/' . ltrim($key, '/'));
        }

        throw new InvalidArgumentException("Disk '{$disk}' is not supported for SignedUrl.");
    }

    public function forPut(string $disk, string $key, string $mime, int $ttlSec = 300): string
    {
        $cfg = config("filesystems.disks.{$disk}");

        if (! $cfg) {
            throw new InvalidArgumentException("Disk '{$disk}' is not defined.");
        }

        if (($cfg['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException("Disk '{$disk}' is not configured for GCS PUT signed URL.");
        }

        [$bucket, $objectPath] = $this->resolveGcs($cfg, $key);
        $object = $bucket->object($objectPath);

        return $object->signedUrl(
            (new DateTimeImmutable())->modify("+{$ttlSec} seconds"),
            [
                'version'     => 'v4',
                'method'      => 'PUT',
                'contentType' => $mime,
            ]
        );
    }

    private function resolveGcs(array $cfg, string $key): array
    {
        $bucket = $this->storage->bucket($cfg['bucket']);
        $prefix = rtrim($cfg['root'] ?? $cfg['path_prefix'] ?? '', '/');

        $objectPath = ltrim(
            ($prefix ? "{$prefix}/" : '') . ltrim($key, '/'),
            '/'
        );

        return [$bucket, $objectPath];
    }
}
