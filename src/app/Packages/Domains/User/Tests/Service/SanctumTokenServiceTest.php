<?php

namespace App\Packages\Domains\User\Tests\Service;

use App\Models\User;
use App\Packages\Domains\User\Factory\UserEntityFactory;
use App\Packages\Domains\User\Service\SanctumTokenService;
use App\Packages\Domains\User\UserEntity;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SanctumTokenServiceTest extends TestCase
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
            DB::connection('mysql')->table('personal_access_tokens')->truncate();
            User::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function service(): SanctumTokenService
    {
        return new SanctumTokenService();
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

    private function buildEntityFromUser(User $user): UserEntity
    {
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

    public function test_createToken_returns_plain_text_token(): void
    {
        $user   = $this->seedUser();
        $entity = $this->buildEntityFromUser($user);

        $token = $this->service()->createToken($entity);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_createToken_throws_when_user_not_found(): void
    {
        $entity = UserEntityFactory::build([
            'id'                      => 999999,
            'first_name'              => 'Ghost',
            'last_name'               => 'User',
            'email'                   => 'ghost@example.com',
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not found.');

        $this->service()->createToken($entity);
    }

    public function test_revokeAllTokens_deletes_tokens_for_user(): void
    {
        $user   = $this->seedUser();
        $entity = $this->buildEntityFromUser($user);

        $user->createToken('token-1');
        $user->createToken('token-2');

        $this->service()->revokeAllTokens($entity);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_revokeAllTokens_throws_when_user_not_found(): void
    {
        $entity = UserEntityFactory::build([
            'id'                      => 999999,
            'first_name'              => 'Ghost',
            'last_name'               => 'User',
            'email'                   => 'ghost@example.com',
            'age_range'               => 'teens',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not found.');

        $this->service()->revokeAllTokens($entity);
    }

    public function test_revokeCurrentToken_deletes_the_given_token(): void
    {
        $user      = $this->seedUser();
        $plainText = $user->createToken('auth-token')->plainTextToken;

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        $this->service()->revokeCurrentToken($plainText);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_revokeCurrentToken_does_nothing_when_token_not_found(): void
    {
        $this->service()->revokeCurrentToken('invalid-token-that-does-not-exist');
    }
}
