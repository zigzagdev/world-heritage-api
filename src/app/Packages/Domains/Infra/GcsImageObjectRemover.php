<?php

namespace App\Packages\Domains\Infra;

use App\Packages\Domains\Ports\ObjectRemovePort;
use InvalidArgumentException;
use Google\Cloud\Storage\StorageClient;

class GcsImageObjectRemover implements ObjectRemovePort
{
    public function __construct(private readonly StorageClient $storage) {}

    public function remove(string $disk, string $key): void
    {
        [$bucket, $path] = $this->resolve($disk, $key);
        try { $bucket->object($path)->delete(); } catch (\Throwable $e) { report($e); }
    }

    public function removeByPrefix(string $disk, string $prefix): int
    {
        [$bucket, $pathPrefix] = $this->resolve($disk, rtrim($prefix, '/').'/');
        $count = 0;
        foreach ($bucket->objects(['prefix' => $pathPrefix]) as $obj) {
            try { $obj->delete(); $count++; } catch (\Throwable $e) { report($e); }
        }
        return $count;
    }

    private function resolve(string $disk, string $key): array
    {
        $cfg = config("filesystems.disks.{$disk}");
        if (!$cfg || ($cfg['driver'] ?? null) !== 'gcs') {
            throw new InvalidArgumentException("Disk '{$disk}' is not configured for GCS.");
        }
        $bucket = $this->storage->bucket($cfg['bucket']);
        $root   = trim((string)($cfg['root'] ?? ''), '/');
        $path   = ltrim(($root ? "{$root}/" : '').ltrim($key, '/'), '/');

        return [$bucket, $path];
    }
}
