<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\CommandUseCases\UseCase\User\UpdateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class UpdateUserUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildCommand(): UpdateUserCommand
    {
        $faker = FakerFactory::create();

        return UpdateUserCommand::fromArray([
            'id'                => $faker->unique()->randomNumber(5),
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => 'free',
        ]);
    }

    private function buildDto(): UserDto
    {
        $faker = FakerFactory::create();

        return new UserDto(
            id:                    $faker->unique()->randomNumber(5),
            firstName:             $faker->firstName(),
            lastName:              $faker->lastName(),
            email:                 $faker->unique()->safeEmail(),
            ageRange:              'teens',
            subscriptionTier:      'free',
            subscriptionExpiresAt: null,
        );
    }

    public function test_handle_passes_entity_to_repository_and_returns_dto(): void
    {
        $command = $this->buildCommand();
        $dto     = $this->buildDto();

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('updateUser')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturn($dto);

        $result = (new UpdateUserUseCase($repository))->handle($command);

        $this->assertSame($dto, $result);
    }
}