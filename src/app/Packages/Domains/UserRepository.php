<?php

namespace App\Packages\Domains;

use App\Models\User;
use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use App\Packages\Features\QueryUseCases\Factory\Dto\UserDtoFactory;
use Exception;

class UserRepository implements UserRepositroyInterface
{
    public function __construct(
        private User $userModel
    ) {}

    public function createUser(UserEntity $entity): UserDto
    {
        $insertUser = $this->userModel->create([
            'email'                   => $entity->getEmail(),
            'first_name'              => $entity->getFirstName(),
            'last_name'               => $entity->getLastName(),
            'password'                => $entity->getPasswordHash(),
            'age_range'               => $entity->getAgeRange()->value,
            'subscription_tier'       => $entity->getSubscription()->getTier()->value,
            'subscription_expires_at' => $entity->getSubscription()->getExpiresAt()?->format('Y-m-d H:i:s'),
        ]);

        if (!$insertUser->wasRecentlyCreated) {
            throw new Exception('Failed to create user.');
        }

        return UserDtoFactory::build($insertUser->toArray());
    }

    public function updateUser(UserEntity $entity): UserDto
    {
        $user = $this->userModel->find($entity->getId());

        if ($user === null) {
            throw new Exception('User not found.');
        }

        $user->update([
            'first_name'              => $entity->getFirstName(),
            'last_name'               => $entity->getLastName(),
            'email'                   => $entity->getEmail(),
            'age_range'               => $entity->getAgeRange()->value,
            'subscription_tier'       => $entity->getSubscription()->getTier()->value,
            'subscription_expires_at' => $entity->getSubscription()->getExpiresAt()?->format('Y-m-d H:i:s'),
        ]);

        $user->refresh();

        return UserDtoFactory::build($user->toArray());
    }
}