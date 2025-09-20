<?php

namespace App\Packages\Domains\Adapter;

use App\Packages\Domains\Ports\ObjectStoragePort;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;

class GcsObjectStorageAdapter implements ObjectStoragePort
{
    public function __construct(
        private readonly StorageClient $storage
    ){}

    public function put(string $disk, string $key, string $mime, $payload): void
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);

        $options = [
            'name' => $objectPath,
            'predefinedAcl' => 'private',
            'metadata' => ['contentType' => $mime],
        ];

        if (is_resource($payload)) {
            $bucket->upload($payload, $options);
        } elseif ($payload instanceof \SplFileInfo) {
            $stream = fopen($payload->getRealPath(), 'rb');
            if ($stream === false) {
                throw new InvalidArgumentException("Cannot open file: {$payload->getRealPath()}");
            }
            $bucket->upload($stream, $options);
            fclose($stream);
        } elseif (is_string($payload)) {
            $bucket->upload($payload, $options);
        } else {
            throw new InvalidArgumentException('Unsupported payload type for put().');
        }
    }

    public function delete(string $disk, string $key): void
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);
        $object = $bucket->object($objectPath);
        try {
            $object->delete();
        } catch (NotFoundException) {
        }
    }

    public function exists(string $disk, string $key): bool
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);
        return $bucket->object($objectPath)->exists();
    }

    /**
     * @return array{\Google\Cloud\Storage\Bucket,string}
     */
    private function resolve(string $disk, string $key): array
    {
        $cfg = config("filesystems.disks.{$disk}");
        if (!$cfg || ($cfg['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException("Disk '{$disk}' is not configured for GCS.");
        }
        $bucket = $this->storage->bucket($cfg['bucket']);
        $prefix = rtrim($cfg['root'] ?? '', '/');
        $objectPath = ltrim(($prefix ? "{$prefix}/" : '') . ltrim($key, '/'), '/');
        return [$bucket, $objectPath];
    }
}
