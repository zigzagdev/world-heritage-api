<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetUserByIdTest extends TestCase
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

    public function test_get_user_by_id_returns_200_with_user_data(): void
    {
        $user = $this->seedUser();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
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
            ->assertJsonFragment([
                'status' => 'success',
                'id'     => $user->id,
                'email'  => $user->email,
            ]);
    }

    public function test_get_user_by_id_returns_404_when_user_not_found(): void
    {
        $response = $this->getJson('/api/v1/users/999999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'User not found.',
            ]);
    }
}