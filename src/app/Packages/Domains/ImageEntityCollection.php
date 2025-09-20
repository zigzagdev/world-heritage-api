<?php

namespace App\Packages\Domains;

class ImageEntityCollection
{
    private array $images = [];

    public function __construct(ImageEntity ...$images)
    {
        $this->images = $images;
    }

    public function getItems(): array
    {
        return $this->images;
    }

    public function add(ImageEntity $image): static
    {
        $this->images[] = $image;
        return $this;
    }
}