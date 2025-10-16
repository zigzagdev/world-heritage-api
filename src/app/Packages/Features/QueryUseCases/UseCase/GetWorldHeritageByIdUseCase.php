<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

class GetWorldHeritageByIdUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService
    ) {}

    public function handle(
        int $id
    ): WorldHeritageDto
    {
        return $this->worldHeritageQueryService->getHeritageById($id);
    }
}