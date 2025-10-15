<?php

namespace App\Packages\Features\QueryUseCases\Dto;

class ImageDtoCollection
{
    private array $images = [];
    public function __construct(ImageDto ...$images)
    {
        $this->images = $images;
    }

    public function add(ImageDto $image): self
    {
        $this->images[] = $image;
        return $this;
    }

    public function all(): array
    {
        return $this->images;
    }

    public function isEmpty(): bool
    {
        return $this->images === [];
    }

    public function first(): ?ImageDto
    {
        return $this->images[0] ?? null;
    }

    public function primary(): ?ImageDto
    {
        foreach ($this->images as $img) {
            if ($img->isPrimary) return $img;
        }
        return $this->first();
    }

    public function sortInPlace(): void
    {
        usort($this->images, static fn(ImageDto $a, ImageDto $b) => $a->sortOrder <=> $b->sortOrder);
    }

    public function toArray(): array
    {
        return array_map(static fn(ImageDto $i) => $i->toArray(), $this->images);
    }

    public function count(): int
    {
        return count($this->images);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function createImageEntityCollectionFrom(array $images): ImageDtoCollection
    {
        $collection = new ImageDtoCollection();
        foreach ($images as $img) {
            $url = 'http://localhost/storage/' . ltrim($img['path'], '/'); // ← URL化

            $collection->add(new ImageDto(
                id:        $img['id'],
                url:       $url,
                sortOrder: (int) $img['sort_order'],
                width:     $img['width'],
                height:    $img['height'],
                format:    $img['format'],
                alt:       $img['alt'],
                credit:    $img['credit'],
                isPrimary: ((int) $img['sort_order']) === 1,  // ← primary を正しく設定
                checksum:  $img['checksum'],
            ));
        }
        return $collection;
    }

}