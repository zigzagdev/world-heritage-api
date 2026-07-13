<?php

namespace App\Packages\Features\QueryUseCases\Dto\User;

final class TokenDto
{
    public function __construct(
        public readonly string $token,
    ) {}
}
