<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\CommandUseCases\UseCase\User\UpdateUserUseCase;
use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;
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

    private function buildCommand(array $overrides = []): UpdateUserCommand
    {
        $faker = FakerFactory::create();

        return UpdateUserCommand::fromArray(array_merge([
            'id'                => $faker->unique()->randomNumber(5),
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => 'free',
        ], $overrides));
    }

    private function buildDto(int $id): UserDto
    {
        $faker = FakerFactory::create();

        return new UserDto(
            id:                    $id,
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
        $existing = $this->buildDto($command->id);
        $updated  = $this->buildDto($command->id);

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with($command->id)
            ->andReturn($existing);
        $repository->shouldReceive('updateUser')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturn($updated);

        $result = (new UpdateUserUseCase($repository))->handle($command);

        $this->assertSame($updated, $result);
    }

    public function test_handle_keeps_existing_value_when_field_is_omitted(): void
    {
        $command  = $this->buildCommand(['first_name' => null]);
        $existing = $this->buildDto($command->id);
        $passedEntity = null;

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with($command->id)
            ->andReturn($existing);
        $repository->shouldReceive('updateUser')
            ->once()
            ->with(Mockery::on(function (UserEntity $entity) use (&$passedEntity) {
                $passedEntity = $entity;

                return true;
            }))
            ->andReturn($existing);

        (new UpdateUserUseCase($repository))->handle($command);

        $this->assertSame($existing->firstName, $passedEntity->getFirstName());
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $command = $this->buildCommand();

        /** @var UserRepositroyInterface|MockInterface $repository */
        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with($command->id)
            ->andReturn(null);
        $repository->shouldNotReceive('updateUser');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        (new UpdateUserUseCase($repository))->handle($command);
    }
}