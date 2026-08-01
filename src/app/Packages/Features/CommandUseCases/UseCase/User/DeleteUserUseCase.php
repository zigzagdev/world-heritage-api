<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;

final class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(int $id): void
    {
        $this->userRepository->deleteUser($id);
    }
}
