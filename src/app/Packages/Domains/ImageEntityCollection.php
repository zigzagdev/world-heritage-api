<?php

namespace App\Packages\Domains;

class ImageEntityCollection
{
    public function __construct(
        private array $images = []
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