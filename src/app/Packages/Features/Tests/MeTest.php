<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use App\Packages\Domains\User\Interface\UserRepositroyInterface;
use App\Packages\Features\QueryUseCases\UseCase\User\GetUserByIdUseCase;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class MeTest extends TestCase
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

    public function test_me_returns_200_with_authenticated_user_when_authenticated(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/user/me');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'success',
                'data'   => [
                    'id'                      => $user->id,
                    'first_name'              => 'John',
                    'last_name'               => 'Doe',
                    'email'                   => 'john@example.com',
                    'age_range'               => '20s',
                    'subscription_tier'       => 'free',
                    'subscription_expires_at' => null,
                ],
            ]);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/user/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_500_on_unexpected_error(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $repository = Mockery::mock(UserRepositroyInterface::class);
        $repository->shouldReceive('findById')
            ->andThrow(new RuntimeException('Unexpected error'));

        $this->app->instance(GetUserByIdUseCase::class, new GetUserByIdUseCase($repository));

        $response = $this->withToken($token)
            ->getJson('/api/v1/user/me');

        $response->assertStatus(500)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ]);
    }
}