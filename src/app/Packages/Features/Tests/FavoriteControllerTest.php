<?php

namespace App\Packages\Features\Tests;

use App\Models\User;
use App\Models\WorldHeritage;
use App\Packages\Domains\Favorite\Interface\FavoriteRepositoryInterface;
use App\Packages\Features\CommandUseCases\UseCase\Favorite\AddFavoriteUseCase;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use App\Packages\Features\QueryUseCases\UseCase\Favorite\GetFavoriteHeritagesUseCase;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
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
            DB::connection('mysql')->table('user_favorite')->truncate();
            DB::connection('mysql')->table('personal_access_tokens')->truncate();
            WorldHeritage::truncate();
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

    private function seedWorldHeritage(int $id): WorldHeritage
    {
        return WorldHeritage::create([
            'id'             => $id,
            'official_name'  => 'Example Site',
            'name'           => 'Example Site',
            'region'         => 'Asia',
            'category'       => 'Cultural',
            'criteria'       => ['i'],
            'year_inscribed' => 2000,
        ]);
    }

    public function test_addFavorite_returns_201_and_persists_favorite_when_authenticated(): void
    {
        $user          = $this->seedUser();
        $worldHeritage = $this->seedWorldHeritage(1);
        $token         = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/favorites', ['world_heritage_id' => $worldHeritage->id]);

        $response->assertStatus(201)
            ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseHas('user_favorite', [
            'user_id'                => $user->id,
            'world_heritage_site_id' => $worldHeritage->id,
        ]);
    }

    public function test_addFavorite_returns_401_when_unauthenticated(): void
    {
        $worldHeritage = $this->seedWorldHeritage(1);

        $response = $this->postJson('/api/v1/favorites', ['world_heritage_id' => $worldHeritage->id]);

        $response->assertStatus(401);
    }

    public function test_addFavorite_returns_500_on_unexpected_error(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $repository->shouldReceive('addFavorite')
            ->andThrow(new RuntimeException('Unexpected error'));

        $this->app->instance(AddFavoriteUseCase::class, new AddFavoriteUseCase($repository));

        $response = $this->withToken($token)
            ->postJson('/api/v1/favorites', ['world_heritage_id' => 1]);

        $response->assertStatus(500)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ]);
    }

    public function test_getFavorites_returns_200_with_favorite_heritages_when_authenticated(): void
    {
        $user           = $this->seedUser();
        $worldHeritage1 = $this->seedWorldHeritage(1);
        $worldHeritage2 = $this->seedWorldHeritage(2);
        $token          = $user->createToken('auth-token')->plainTextToken;

        $user->favorites()->attach($worldHeritage1->id);
        $user->favorites()->attach($worldHeritage2->id);

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonCount(2, 'data');

        $ids = array_column($response->json('data'), 'id');
        $this->assertEqualsCanonicalizing([$worldHeritage1->id, $worldHeritage2->id], $ids);
    }

    public function test_getFavorites_returns_200_with_empty_array_when_user_has_no_favorites(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonCount(0, 'data');
    }

    public function test_getFavorites_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/favorites');

        $response->assertStatus(401);
    }

    public function test_getFavorites_returns_500_on_unexpected_error(): void
    {
        $user  = $this->seedUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $favoriteRepository = Mockery::mock(FavoriteRepositoryInterface::class);
        $favoriteRepository->shouldReceive('getFavoriteWorldHeritageIds')
            ->andThrow(new RuntimeException('Unexpected error'));

        $worldHeritageQueryService = Mockery::mock(WorldHeritageQueryServiceInterface::class);

        $this->app->instance(
            GetFavoriteHeritagesUseCase::class,
            new GetFavoriteHeritagesUseCase($favoriteRepository, $worldHeritageQueryService),
        );

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertStatus(500)
            ->assertJsonFragment([
                'status'  => 'error',
                'message' => 'Internal Server Error',
            ]);
    }
}
