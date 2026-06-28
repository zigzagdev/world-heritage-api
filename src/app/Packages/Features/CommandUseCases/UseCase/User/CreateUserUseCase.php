<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(CreateUserCommand $command): UserDto
    {
        return $this->userRepository->createUser($command);
    }
}