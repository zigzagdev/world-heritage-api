<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;

final class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(UpdateUserCommand $command): UserDto
    {
        $entity = UserEntityFactory::build([
            'id'                      => $command->id,
            'first_name'              => $command->firstName,
            'last_name'               => $command->lastName,
            'email'                   => $command->email,
            'age_range'               => $command->ageRange,
            'subscription_tier'       => $command->subscriptionTier,
            'subscription_expires_at' => $command->subscriptionExpiresAt,
        ]);

        return $this->userRepository->updateUser($entity);
    }
}