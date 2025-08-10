<?php

namespace App\Common\Pagination;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;

class PaginationDto
{
    private WorldHeritageDtoCollection $collection;
    private array $pagination;

    public function __construct(
        WorldHeritageDtoCollection $collection,
        array $pagination
    ) {
        $this->collection = $collection;
        $this->pagination = $pagination;
    }

    public function getCollection(): WorldHeritageDtoCollection
    {
        return $this->collection;
    }

    public function getCurrentPage(): ?int
    {
        return $this->pagination['current_page'] ?? null;
    }

    public function getFrom(): ?string
    {
        return $this->pagination['from'] ?? null;
    }

    public function getTo(): ?string
    {
        return $this->pagination['to'] ?? null;
    }

    public function getPerPage(): ?int
    {
        return $this->pagination['per_page'] ?? null;
    }

    public function getPath(): ?string
    {
        return $this->pagination['path'] ?? null;
    }

    public function getLastPage(): ?int
    {
        return $this->pagination['last_page'] ?? null;
    }

    public function getTotal(): ?int
    {
        return $this->pagination['total'] ?? null;
    }

    public function getFirstPageUrl(): ?string
    {
        return $this->pagination['first_page_url'] ?? null;
    }

    public function getLastPageUrl(): ?string
    {
        return $this->pagination['last_page_url'] ?? null;
    }

    public function getNextPageUrl(): ?string
    {
        return $this->pagination['next_page_url'] ?? null;
    }

    public function getPrevPageUrl(): ?string
    {
        return $this->pagination['prev_page_url'] ?? null;
    }

    public function getLinks(): ?array
    {
        return $this->pagination['links'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->collection->toArray(),
            'pagination' => [
                'current_page' => $this->getCurrentPage(),
                'from' => $this->getFrom(),
                'to' => $this->getTo(),
                'per_page' => $this->getPerPage(),
                'path' => $this->getPath(),
                'last_page' => $this->getLastPage(),
                'total' => $this->getTotal(),
                'first_page_url' => $this->getFirstPageUrl(),
                'last_page_url' => $this->getLastPageUrl(),
                'next_page_url' => $this->getNextPageUrl(),
                'prev_page_url' => $this->getPrevPageUrl(),
                'links' => $this->getLinks(),
            ],
        ];
    }
}