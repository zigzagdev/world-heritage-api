<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateUserTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        $faker = FakerFactory::create();

        return array_merge([
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'password'          => $faker->password(8),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => $faker->randomElement(['free', 'premium']),
        ], $overrides);
    }

    public function test_create_user_returns_201_with_user_data(): void
    {
        $response = $this->postJson('/api/v1/user/create', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'age_range',
                    'subscription_tier',
                    'subscription_expires_at',
                ],
            ])
            ->assertJsonFragment(['status' => 'success']);
    }

    public function test_create_user_returns_500_when_required_key_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['email']);

        $response = $this->postJson('/api/v1/user/create', $payload);

        $response->assertStatus(500)
            ->assertJsonFragment(['status' => 'error']);
    }

    public function test_create_user_returns_500_on_duplicate_email(): void
    {
        $payload = $this->validPayload();

        $this->postJson('/api/v1/user/create', $payload);
        $response = $this->postJson('/api/v1/user/create', $payload);

        $response->assertStatus(500)
            ->assertJsonFragment(['status' => 'error']);
    }
}