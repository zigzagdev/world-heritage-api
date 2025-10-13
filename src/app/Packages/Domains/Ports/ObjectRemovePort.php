<?php

namespace App\Packages\Domains\Ports;

interface ObjectRemovePort
{
    public function remove(string $disk, string $key): void;

    public function removeByPrefix(string $disk, string $prefix): void;
}