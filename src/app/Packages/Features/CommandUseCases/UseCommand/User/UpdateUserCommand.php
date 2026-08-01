<?php

namespace App\Packages\Features\CommandUseCases\UseCommand\User;

use InvalidArgumentException;

final class UpdateUserCommand
{
    private function __construct(
        public readonly int     $id,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?string $ageRange,
        public readonly ?string $subscriptionTier,
        public readonly ?string $subscriptionExpiresAt,
    ) {}

    // Fields other than `id` are optional: this supports partial updates,
    // where an omitted field means "keep the current value" (resolved by
    // UpdateUserUseCase), rather than requiring the full user payload.
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('id', $data)) {
            throw new InvalidArgumentException('Missing required key: id');
        }

        return new self(
            id:                    (int) $data['id'],
            firstName:             $data['first_name'] ?? null,
            lastName:              $data['last_name'] ?? null,
            email:                 $data['email'] ?? null,
            ageRange:              $data['age_range'] ?? null,
            subscriptionTier:      $data['subscription_tier'] ?? null,
            subscriptionExpiresAt: $data['subscription_expires_at'] ?? null,
        );
    }
}