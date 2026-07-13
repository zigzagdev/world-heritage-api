<?php

namespace App\Packages\Features\QueryUseCases\Dto\User;

final class AuthTokenDto
{
    public function __construct(
        public readonly string $token,
        public readonly string $tokenType = 'Bearer',
    ) {}
}
