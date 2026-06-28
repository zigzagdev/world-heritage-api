<?php

namespace App\Packages\Domains;

use App\Models\User;
use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use App\Packages\Features\QueryUseCases\Factory\Dto\UserDtoFactory;
use Exception;

class UserRepository implements UserRepositroyInterface
{
    public function __construct(
        private User $userModel
    ) {}

    public function createUser(CreateUserCommand $command): UserDto
    {
        $insertUser = $this->userModel->create([
            'email'                   => $command->email,
            'first_name'              => $command->firstName,
            'last_name'               => $command->lastName,
            'password'                => bcrypt($command->password),
            'age_range'               => $command->ageRange,
            'subscription_tier'       => $command->subscriptionTier,
            'subscription_expires_at' => $command->subscriptionExpiresAt,
        ]);

        if (!$insertUser->wasRecentlyCreated) {
            throw new Exception('Failed to create user.');
        }

        return UserDtoFactory::build($insertUser->toArray());
    }
}