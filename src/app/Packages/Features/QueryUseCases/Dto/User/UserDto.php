<?php

namespace App\Packages\Features\QueryUseCases\Dto\User;

final class UserDto
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly string  $email,
        public readonly string  $ageRange,
        public readonly string  $subscriptionTier,
        public readonly ?string $subscriptionExpiresAt,
    ) {}
}