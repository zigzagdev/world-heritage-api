<?php

namespace App\Packages\Features\QueryUseCases\UseCase\User;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;

final class GetUserByIdUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(int $id): UserDto
    {
        $userDto = $this->userRepository->findById($id);

        if ($userDto === null) {
            throw new Exception('User not found.');
        }

        return $userDto;
    }
}
