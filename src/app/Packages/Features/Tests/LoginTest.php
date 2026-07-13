<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
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

    private function seedUser(string $email, string $password): User
    {
        return User::create([
            'first_name'              => 'John',
            'last_name'               => 'Doe',
            'email'                   => $email,
            'password'                => bcrypt($password),
            'age_range'               => '20s',
            'subscription_tier'       => 'free',
            'subscription_expires_at' => null,
        ]);
    }

    public function test_login_returns_200_with_token_on_valid_credentials(): void
    {
        $this->seedUser('john@example.com', 'secret123');

        $response = $this->postJson('/api/v1/user/login', [
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['token', 'token_type'],
            ])
            ->assertJsonFragment([
                'status'     => 'success',
                'token_type' => 'Bearer',
            ]);
    }

    public function test_login_returns_401_when_user_not_found(): void
    {
        $response = $this->postJson('/api/v1/user/login', [
            'email'    => 'notfound@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['status' => 'error']);
    }

    public function test_login_returns_401_on_wrong_password(): void
    {
        $this->seedUser('john@example.com', 'secret123');

        $response = $this->postJson('/api/v1/user/login', [
            'email'    => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['status' => 'error']);
    }
}
