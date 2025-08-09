<?php

namespace App\Common\Pagination;

use App\Packages\Features\QueryUseCases\ViewModel\WorldHeritageViewModel;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

class PaginationViewModel
{
    public function __construct(
        private readonly PaginationDto $pagination
    ) {}

    public function toArray(): array
    {
        $items = array_map(
            fn (WorldHeritageDto $dto) => (new WorldHeritageViewModel($dto))->toArray(),
            $this->pagination->getCollection()
        );

        return [
            'data'           => $items,
            'current_page'   => $this->pagination->getCurrentPage(),
            'from'           => $this->pagination->getFrom(),
            'to'             => $this->pagination->getTo(),
            'per_page'       => $this->pagination->getPerPage(),
            'path'           => $this->pagination->getPath(),
            'last_page'      => $this->pagination->getLastPage(),
            'total'          => $this->pagination->getTotal(),
            'first_page_url' => $this->pagination->getFirstPageUrl(),
            'last_page_url'  => $this->pagination->getLastPageUrl(),
            'next_page_url'  => $this->pagination->getNextPageUrl(),
            'prev_page_url'  => $this->pagination->getPrevPageUrl(),
            'links'          => $this->pagination->getLinks(),
        ];
    }
}
