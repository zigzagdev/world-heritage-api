<?php

namespace App\Packages\Domains\User;

use App\Models\User;
use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Domains\User\ValueObject\Email;
use App\Packages\Domains\User\Factory\UserEntityFactory;
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
            'email'                   => $entity->getEmail()->value(),
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

    public function findById(int $id): ?UserDto
    {
        $user = $this->userModel->find($id);

        if ($user === null) {
            return null;
        }

        return UserDtoFactory::build($user->toArray());
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
            'email'                   => $entity->getEmail()->value(),
            'age_range'               => $entity->getAgeRange()->value,
            'subscription_tier'       => $entity->getSubscription()->getTier()->value,
            'subscription_expires_at' => $entity->getSubscription()->getExpiresAt()?->format('Y-m-d H:i:s'),
        ]);

        $user->refresh();

        return UserDtoFactory::build($user->toArray());
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userModel->find($id);

        if ($user === null) {
            throw new Exception('User not found.');
        }

        $user->delete();
    }

    public function findByEmail(Email $email): ?UserEntity
    {
        $user = $this->userModel->where('email', $email->value())->first();

        if ($user === null) {
            return null;
        }

        return UserEntityFactory::build([
            'id'                      => $user->id,
            'first_name'              => $user->first_name,
            'last_name'               => $user->last_name,
            'email'                   => $user->email,
            'age_range'               => $user->age_range,
            'subscription_tier'       => $user->subscription_tier,
            'subscription_expires_at' => $user->subscription_expires_at,
            'password_hash'           => $user->password,
        ]);
    }
}