<?php

namespace App\Packages\Domains\Tests;

use App\Models\User;
use App\Packages\Domains\UserRepository;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Exception;
use Faker\Factory as FakerFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function buildEntity(): UserEntity
    {
        $faker = FakerFactory::create();

        return UserEntityFactory::build([
            'id'                      => null,
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier'       => $faker->randomElement(['free', 'premium']),
            'subscription_expires_at' => null,
            'password_hash'           => 'hashed_password',
        ]);
    }

    private function buildAttributes(): array
    {
        $faker = FakerFactory::create();

        return [
            'id'                      => $faker->unique()->randomNumber(5),
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ];
    }

    private function buildCreateModelMock(bool $wasRecentlyCreated): User
    {
        $attributes = $this->buildAttributes();

        /** @var User|MockInterface $createdUser */
        $createdUser = Mockery::mock(User::class);
        $createdUser->wasRecentlyCreated = $wasRecentlyCreated;
        $createdUser->shouldReceive('toArray')->andReturn($attributes);

        /** @var User|MockInterface $userModel */
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('create')->andReturn($createdUser);

        return $userModel;
    }

    private function buildUpdateModelMock(?int $foundId): User
    {
        $attributes = $this->buildAttributes();

        /** @var User|MockInterface $userModel */
        $userModel = Mockery::mock(User::class);

        if ($foundId === null) {
            $userModel->shouldReceive('find')->andReturn(null);
        } else {
            /** @var User|MockInterface $foundUser */
            $foundUser = Mockery::mock(User::class)->makePartial();
            $foundUser->shouldReceive('update')->andReturn(true);
            $foundUser->shouldReceive('refresh')->andReturn(null);
            $foundUser->shouldReceive('toArray')->andReturn($attributes);

            $userModel->shouldReceive('find')->with($foundId)->andReturn($foundUser);
        }

        return $userModel;
    }

    public function test_createUser_returns_user_dto_on_success(): void
    {
        $repository = new UserRepository($this->buildCreateModelMock(wasRecentlyCreated: true));
        $result     = $repository->createUser($this->buildEntity());

        $this->assertInstanceOf(UserDto::class, $result);
    }

    public function test_createUser_throws_exception_when_insert_fails(): void
    {
        $repository = new UserRepository($this->buildCreateModelMock(wasRecentlyCreated: false));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create user.');

        $repository->createUser($this->buildEntity());
    }

    public function test_updateUser_returns_user_dto_on_success(): void
    {
        $existingId = 1;
        $entity     = UserEntityFactory::build([
            'id'                      => $existingId,
            'first_name'              => 'Updated',
            'last_name'               => 'Name',
            'email'                   => 'updated@example.com',
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);

        $repository = new UserRepository($this->buildUpdateModelMock(foundId: $existingId));
        $result     = $repository->updateUser($entity);

        $this->assertInstanceOf(UserDto::class, $result);
    }

    public function test_updateUser_throws_exception_when_user_not_found(): void
    {
        $entity = UserEntityFactory::build([
            'id'                      => 999,
            'first_name'              => 'Ghost',
            'last_name'               => 'User',
            'email'                   => 'ghost@example.com',
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);

        $repository = new UserRepository($this->buildUpdateModelMock(foundId: null));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        $repository->updateUser($entity);
    }
}