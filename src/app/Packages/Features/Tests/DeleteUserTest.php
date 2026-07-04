<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeleteUserTest extends TestCase
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

    public function test_delete_user_returns_200_when_user_exists(): void
    {
        $user = $this->seedUser();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_user_returns_404_when_user_not_found(): void
    {
        $response = $this->deleteJson('/api/v1/users/999999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'User not found.',
            ]);
    }
}
