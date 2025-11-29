<?php

namespace App\Packages\Domains\Adapter;

use App\Packages\Domains\Ports\ObjectStoragePort;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

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
        $cfg = config("filesystems.disks.{$disk}");
        if (! $cfg) {
            throw new InvalidArgumentException("Disk '{$disk}' is not defined.");
        }

        $driver = $cfg['driver'] ?? null;

        if ($driver === 'gcs') {
            [$bucket, $objectPath] = $this->resolveGcs($cfg, $key);
            $object = $bucket->object($objectPath);
            try {
                $object->delete();
            } catch (NotFoundException) {
            }
            return;
        }

        if ($driver === 'local') {
            Storage::disk($disk)->delete($key);
            return;
        }

        throw new InvalidArgumentException("Disk '{$disk}' is not supported for ObjectStorageAdapter.");
    }

    public function exists(string $disk, string $key): bool
    {
        [$bucket, $objectPath] = $this->resolve($disk, $key);
        return $bucket->object($objectPath)->exists();
    }

    private function resolve(string $disk, string $key): array
    {
        $config = config("filesystems.disks.{$disk}");

        if (! $config) {
            throw new InvalidArgumentException("Disk '{$disk}' is not defined.");
        }

        if (($config['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException("Disk '{$disk}' is not configured for GCS.");
        }

        $bucket = $this->storage->bucket($config['bucket']);
        $prefix = rtrim($config['root'] ?? $config['path_prefix'] ?? '', '/');

        $objectPath = ltrim(
            ($prefix ? "{$prefix}/" : '') . ltrim($key, '/'),
            '/'
        );

        return [$bucket, $objectPath];
    }

    private function resolveGcs(array $cfg, string $key): array
    {
        if (($cfg['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException('resolveGcs called with non-gcs disk config.');
        }

        $bucket = $this->storage->bucket($cfg['bucket']);
        $prefix = rtrim($cfg['root'] ?? $cfg['path_prefix'] ?? '', '/');

        $objectPath = ltrim(
            ($prefix ? "{$prefix}/" : '') . ltrim($key, '/'),
            '/'
        );

        return [$bucket, $objectPath];
    }
}
