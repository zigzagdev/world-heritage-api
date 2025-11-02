<?php

namespace App\Packages\Features\QueryUseCases\UseCase;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;

class GetWorldHeritagesUseCase
{
    public function __construct(
        private readonly WorldHeritageQueryServiceInterface $worldHeritageQueryService
    ){}

    public function handle(): WorldHeritageDtoCollection
    {
        return $this->worldHeritageQueryService->getAllHeritages();
    }
}