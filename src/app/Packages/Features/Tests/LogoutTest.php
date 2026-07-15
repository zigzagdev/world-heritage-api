<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use App\Packages\Features\CommandUseCases\UseCase\User\LogoutUseCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class LogoutTest extends TestCase
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

    private function seedUser(): User
    {
        return User::create([
            'first_name'              => 'John',
            'last_name'               => 'Doe',
            'email'                   => 'john@example.com',
            'password'                => bcrypt('secret123'),
            'age_range'               => '20s',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);
    }

    public function test_logout_returns_200_when_authenticated(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/user/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_logout_returns_401_when_unauthenticated(): void
    {
        $response = $this->postJson('/api/v1/user/logout');

        $response->assertStatus(401);
    }

    public function test_logout_returns_500_on_unexpected_error(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->mock(LogoutUseCase::class)
            ->shouldReceive('handle')
            ->andThrow(new RuntimeException('Unexpected error'));

        $response = $this->withToken($token)
            ->postJson('/api/v1/user/logout');

        $response->assertStatus(500)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ]);
    }
}
