<?php

namespace App\Packages\Features\QueryUseCases\UseCase;
use App\Packages\Domains\Ports\ObjectRemovePort;
use App\Packages\Domains\WorldHeritageRepositoryInterface;

class DeleteWorldHeritageUseCase
{
    public function __construct(
        private readonly WorldHeritageRepositoryInterface $repository,
        private readonly ObjectRemovePort $removePort,
    ) {}

    public function handle(
        int $id
    ): void {

        $this->removePort->removeByPrefix('gcs', "heritages/{$id}/");

        $this->repository->deleteOneHeritage($id);
    }
}