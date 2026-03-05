<?php

namespace App\Packages\Features\QueryUseCases\Dto;

class ImageDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $url,
        public readonly int $sortOrder,
        public readonly bool $isPrimary,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'sort_order' => $this->sortOrder,
            'is_primary' => $this->isPrimary,
        ];
    }
}