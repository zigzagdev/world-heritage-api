<?php
namespace App\Packages\Domains\Ports;

interface ObjectStoragePort {
    public function put(string $disk, string $key, string $mime, $payload): void;

    public function exists(string $disk, string $key): bool;
}
