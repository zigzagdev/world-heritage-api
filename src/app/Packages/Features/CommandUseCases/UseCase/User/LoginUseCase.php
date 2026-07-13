<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\ValueObject\Email;
use App\Packages\Features\QueryUseCases\Dto\User\TokenDto;
use InvalidArgumentException;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
        private readonly TokenServiceInterface   $tokenService,
    ) {}

    public function handle(string $email, string $password): TokenDto
    {
        $entity = $this->userRepository->findByEmail(new Email($email));

        if ($entity === null) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        if (!password_verify($password, $entity->getPasswordHash())) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        $token = $this->tokenService->createToken($entity);

        return new TokenDto(token: $token);
    }
}
