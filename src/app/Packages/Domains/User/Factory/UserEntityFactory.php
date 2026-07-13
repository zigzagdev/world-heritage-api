<?php

namespace App\Packages\Domains\User\Factory;

use App\Packages\Domains\User\Factory\SubscriptionFactory;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Domains\User\AgeRange;
use App\Packages\Domains\User\ValueObject\Email;

final class UserEntityFactory
{
    public static function build(array $data): UserEntity
    {
        $subscription = SubscriptionFactory::build([
            'tier' => $data['subscription_tier'],
            'expires_at' => $data['subscription_expires_at'] ?? null,
            'now' => $data['now'] ?? null,
        ]);

        return new UserEntity(
            id: isset($data['id']) ? (int) $data['id'] : null,
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: new Email($data['email']),
            ageRange: AgeRange::from($data['age_range']),
            subscription: $subscription,
            passwordHash: $data['password_hash'] ?? null,
        );
    }
}