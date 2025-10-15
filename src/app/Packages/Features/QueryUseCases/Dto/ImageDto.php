<?php

namespace App\Packages\Features\QueryUseCases\Dto;

class ImageDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $url,
        public readonly int $sortOrder,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?string $format,
        public readonly ?string $alt,
        public readonly ?string $credit,
        public readonly bool $isPrimary,
        public readonly ?string $checksum = null,
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

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'url'        => $this->url,
            'sort_order' => $this->sortOrder,
            'width'      => $this->width,
            'height'     => $this->height,
            'format'     => $this->format,
            'alt'        => $this->alt,
            'credit'     => $this->credit,
            'is_primary' => $this->isPrimary,
            'checksum'   => $this->checksum,
        ];
    }
}