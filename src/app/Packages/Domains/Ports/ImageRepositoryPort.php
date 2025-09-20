<?php
namespace App\Packages\Domains\Ports;

use App\Packages\Domains\ImageEntity;

interface ImageRepositoryPort {
    public function findById(int $id): ?ImageEntity;
    public function save(ImageEntity $e): ImageEntity;
    public function deleteById(int $id): void;

}
