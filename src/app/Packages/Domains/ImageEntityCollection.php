<?php

namespace App\Packages\Domains;

class ImageEntityCollection
{
    private array $images = [];

    public function __construct(
        ImageEntity ...$images
    ) {}

    public function getItems(): array
    {
        return $this->images;
    }

    public function add(ImageEntity $heritage): static
    {
        $this->images[] = $heritage;

        return $this;
    }
}