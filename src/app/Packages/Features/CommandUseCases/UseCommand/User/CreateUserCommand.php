<?php

namespace App\Packages\Features\CommandUseCases\UseCommand\User;

use InvalidArgumentException;

final class CreateUserCommand
{
    private const REQUIRED_KEYS = [
        'first_name',
        'last_name',
        'email',
        'age_range',
        'subscription_tier',
    ];

    private function __construct(
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly string  $email,
        public readonly string  $ageRange,
        public readonly string  $subscriptionTier,
        public readonly ?string $subscriptionExpiresAt,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing required key: {$key}");
            }
        }

        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            ageRange: $data['age_range'],
            subscriptionTier: $data['subscription_tier'],
            subscriptionExpiresAt: $data['subscription_expires_at'] ?? null,
        );
    }
}