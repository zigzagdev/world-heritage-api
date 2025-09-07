<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Domains\WorldHeritageRepositoryInterface;

class DeleteWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository,
    ) {}

    public function handle(
        int $id
    ): void {
        $this->repository->deleteOneHeritage($id);
    }
}