<?php

namespace App\Packages\Domains\User\Factory;

use App\Packages\Domains\User\Factory\SubscriptionFactory;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Domains\User\AgeRange;

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
            $data['id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            AgeRange::from($data['age_range']),
            $subscription,
        );
    }
}