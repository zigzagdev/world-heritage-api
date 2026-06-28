<?php

namespace App\Packages\Domains\Tests;

use App\Models\User;
use App\Packages\Domains\UserRepository;
use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildCommand(): CreateUserCommand
    {
        $faker = FakerFactory::create();

        return CreateUserCommand::fromArray([
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'password'                => $faker->password(8),
            'age_range'               => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier'       => $faker->randomElement(['free', 'premium']),
            'subscription_expires_at' => null,
        ]);
    }

    private function buildUserModelMock(bool $wasRecentlyCreated): User
    {
        $faker = FakerFactory::create();

        $attributes = [
            'id'                      => $faker->unique()->randomNumber(5),
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ];

        /** @var User|MockInterface $createdUser */
        $createdUser = Mockery::mock(User::class);
        $createdUser->wasRecentlyCreated = $wasRecentlyCreated;
        $createdUser->shouldReceive('toArray')->andReturn($attributes);

        /** @var User|MockInterface $userModel */
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('create')->andReturn($createdUser);

        return $userModel;
    }

    public function test_createUser_returns_user_dto_on_success(): void
    {
        $repository = new UserRepository($this->buildUserModelMock(wasRecentlyCreated: true));
        $result     = $repository->createUser($this->buildCommand());

        $this->assertInstanceOf(UserDto::class, $result);
    }

    public function test_createUser_throws_exception_when_insert_fails(): void
    {
        $repository = new UserRepository($this->buildUserModelMock(wasRecentlyCreated: false));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create user.');

        $repository->createUser($this->buildCommand());
    }
}