<?php

namespace App\Packages\Features\CommandUseCases\UseCase\User;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;

final class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositroyInterface $userRepository,
    ) {}

    public function handle(UpdateUserCommand $command): UserDto
    {
        $existing = $this->userRepository->findById($command->id);

        if ($existing === null) {
            throw new Exception('User not found.');
        }

        $entity = UserEntityFactory::build([
            'id'                      => $command->id,
            'first_name'              => $command->firstName ?? $existing->firstName,
            'last_name'               => $command->lastName ?? $existing->lastName,
            'email'                   => $command->email ?? $existing->email,
            'age_range'               => $command->ageRange ?? $existing->ageRange,
            'subscription_tier'       => $command->subscriptionTier ?? $existing->subscriptionTier,
            'subscription_expires_at' => $command->subscriptionExpiresAt ?? $existing->subscriptionExpiresAt,
        ]);

        return $this->userRepository->updateUser($entity);
    }
}