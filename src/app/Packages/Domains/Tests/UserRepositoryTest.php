<?php

namespace App\Packages\Domains\Tests;

use App\Models\User;
use App\Packages\Domains\User\AgeRange;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\Subscription\Subscription;
use App\Packages\Domains\User\Subscription\SubscriptionTier;
use App\Packages\Domains\User\UserEntity;
use App\Packages\Domains\UserRepository;
use App\Packages\Features\QueryUseCases\Dto\User\UserDto;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->truncate();
    }

    protected function tearDown(): void
    {
        $this->truncate();
        parent::tearDown();
    }

    private function truncate(): void
    {
        if (env('APP_ENV') === 'testing') {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            User::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function repository(): UserRepository
    {
        return new UserRepository(new User());
    }

    private function seedUser(array $overrides = []): User
    {
        $faker = FakerFactory::create();

        return User::create(array_merge([
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'password'                => bcrypt('password'),
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ], $overrides));
    }

    private function buildEntity(array $overrides = []): UserEntity
    {
        $faker = FakerFactory::create();

        return UserEntityFactory::build(array_merge([
            'id'                      => null,
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
            'password_hash'           => bcrypt('password'),
        ], $overrides));
    }

    public function test_createUser_persists_user_and_returns_dto(): void
    {
        $result = $this->repository()->createUser($this->buildEntity());

        $this->assertInstanceOf(UserDto::class, $result);
        $this->assertDatabaseHas('users', ['email' => $result->email]);
    }

    public function test_findById_returns_user_dto_when_user_exists(): void
    {
        $user   = $this->seedUser();
        $result = $this->repository()->findById($user->id);

        $this->assertInstanceOf(UserDto::class, $result);
        $this->assertSame($user->id, $result->id);
        $this->assertSame($user->email, $result->email);
    }

    public function test_findById_returns_null_when_user_not_found(): void
    {
        $result = $this->repository()->findById(999999);

        $this->assertNull($result);
    }

    public function test_updateUser_persists_changes_and_returns_dto(): void
    {
        $user   = $this->seedUser(['email' => 'before@example.com']);
        $entity = $this->buildEntity([
            'id'    => $user->id,
            'email' => 'after@example.com',
        ]);

        $result = $this->repository()->updateUser($entity);

        $this->assertInstanceOf(UserDto::class, $result);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'after@example.com']);
    }

    public function test_updateUser_throws_exception_when_user_not_found(): void
    {
        $entity = $this->buildEntity(['id' => 999999]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found.');

        $this->repository()->updateUser($entity);
    }
}