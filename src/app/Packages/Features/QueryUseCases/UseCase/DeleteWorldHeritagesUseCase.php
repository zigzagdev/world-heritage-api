<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageRepositoryInterface;

class DeleteWorldHeritagesUseCase
{
    public function __construct(
      private readonly WorldHeritageRepositoryInterface $repository
    ){}

    public function handle(array $ids): void
     {
        $this->repository->deleteManyHeritages($ids);
     }
}