<?php

namespace App\Packages\Domains\User;

use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use DateTimeImmutable;

final class UserEntityFactory
{
    public static function build(array $data): UserEntity
    {
        $subscription = new Subscription(
            SubscriptionTier::from($data['subscription_tier']),
            $data['subscription_expires_at'] ?? null,
            $data['now'] ?? null,
        );

        return new UserEntity(
            $data['id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            AgeRange::from($data['age_range']),
            $subscription,
        );
    }
}