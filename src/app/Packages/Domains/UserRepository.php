<?php

namespace App\Packages\Domains;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Models\User;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;

class UserRepository implements UserRepositroyInterface
{
    public function __construct(
        private User $userModel
    ) {}

    public function createUser(
        UserEntity $entity
    ): UserDto
    {
        $insertUser = $this->userModel->create([
            'email'                   => $entity->getEmail(),
            'first_name'              => $entity->getFirstName(),
            'last_name'               => $entity->getLastName(),
            'age_range'               => $entity->getAgeRange()->value,
            'subscription_tier'       => $entity->getSubscription()->getTier()->value,
            'subscription_expires_at' => $entity->getSubscription()->getExpiresAt(),
        ]);

        if (!$insertUser->wasRecentlyCreated) {
            throw new Exception('Failed to create user.');
        }

        return new UserDto();

    }
}