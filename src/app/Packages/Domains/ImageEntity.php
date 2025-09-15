<?php

namespace App\Packages\Domains;

class ImageEntity
{
    public function __construct(
        readonly public ?int $id,
        readonly public string $disk,
        readonly public string $path,
        readonly public ?int $width,
        readonly public ?int $height,
        readonly public ?string $format,
        readonly public ?string $checksum,
        readonly public int $sortOrder,
        readonly public ?string $alt,
        readonly public ?string $credit,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getPath(): string
    {
        return $this->path;
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

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }
}