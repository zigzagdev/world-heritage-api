<?php
namespace App\Packages\Domains\Ports;

interface SignedUrlPort {
    public function forGet(string $disk, string $key, int $ttlSec = 300): string;

    public function forPut(string $disk, string $key, string $mime, int $ttlSec = 300): string;
}

