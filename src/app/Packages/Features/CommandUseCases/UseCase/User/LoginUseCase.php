<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\ValueObject\Email;
use App\Packages\Features\QueryUseCases\Dto\User\AuthTokenDto;
use InvalidArgumentException;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
        private readonly TokenServiceInterface   $tokenService,
    ) {}

    public function handle(string $email, string $password): AuthTokenDto
    {
        $entity = $this->userRepository->findByEmail(new Email($email));

        if ($entity === null) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        if (!password_verify($password, $entity->getPasswordHash())) {
            throw new InvalidArgumentException('Invalid user data. Please try again.');
        }

        $token = $this->tokenService->createToken($entity);

        return new AuthTokenDto(token: $token);
    }
}
