<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\TokenServiceInterface;

class LogoutUseCase
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
    ) {}

    public function handle(string $plainTextToken): void
    {
        $this->tokenService->revokeCurrentToken($plainTextToken);
    }
}
