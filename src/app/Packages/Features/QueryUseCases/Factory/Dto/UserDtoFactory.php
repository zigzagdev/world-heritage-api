<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

class UserDtoFactory
{
    public static function build(array $data): UserDto
    {
        return new UserDto(
            id: (int)$data['id'],
            firstName: (string)$data['first_name'],
            lastName: (string)$data['last_name'],
            email: (string)$data['email'],
            ageRange: (string)$data['age_range'],
            subscriptionTier: (string)$data['subscription_tier'],
            subscriptionExpiresAt: $data['subscription_expires_at'] ?? null,
        );
    }
}
