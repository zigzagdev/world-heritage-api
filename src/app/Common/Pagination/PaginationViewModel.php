<?php

namespace App\Common\Pagination;

use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModelCollection;

class PaginationViewModel
{
    public function __construct(
        private readonly PaginationDto $pagination,
        private readonly ?WorldHeritageViewModelCollection $viewModelCollection = null
    ) {}

    public function toArray(): array
    {
        $items = [];
        if ($this->viewModelCollection) {
            $items = $this->viewModelCollection->toArray();
        } else {
            $dtoCollection = $this->pagination->getCollection();

            if (method_exists($dtoCollection, 'getHeritages')) {
                $items = array_map(
                    fn($dto) => method_exists($dto, 'toArray') ? $dto->toArray() : (array) $dto,
                    $dtoCollection->getHeritages()
                );
            }
        }

        $links = [
            'first' => $this->pagination->getFirstPageUrl(),
            'last' => $this->pagination->getLastPageUrl(),
            'next' => $this->pagination->getNextPageUrl(),
            'prev' => $this->pagination->getPrevPageUrl(),
        ];

        $meta = [
            'current_page' => (int) $this->pagination->getCurrentPage(),
            'per_page'  => (int) $this->pagination->getPerPage(),
            'from' => (int) $this->pagination->getFrom(),
            'to' => (int) $this->pagination->getTo(),
            'path' => (string) $this->pagination->getPath(),
            'last_page' => (int) $this->pagination->getLastPage(),
            'total' => (int) $this->pagination->getTotal(),
            'links' => $this->pagination->getLinks(),
        ];

        return [
            'data'  => $items,
            'links' => $links,
            'meta'  => $meta,
        ];
    }
}
