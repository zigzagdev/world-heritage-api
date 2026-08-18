<?php

namespace App\Packages\Domains\Favorite\Tests;

use App\Models\User;
use App\Models\WorldHeritage;
use App\Packages\Domains\Favorite\FavoriteRepository;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FavoriteRepositoryTest extends TestCase
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
            WorldHeritage::truncate();
            User::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function repository(): FavoriteRepository
    {
        return new FavoriteRepository(new User());
    }

    private function seedUser(): User
    {
        $faker = FakerFactory::create();

        return User::create([
            'first_name'              => $faker->firstName(),
            'last_name'               => $faker->lastName(),
            'email'                   => $faker->unique()->safeEmail(),
            'password'                => bcrypt('password'),
            'age_range'               => 'teens',
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

    public function test_addFavorite_attaches_world_heritage_site_to_user(): void
    {
        $user          = $this->seedUser();
        $worldHeritage = $this->seedWorldHeritage(1);

        $this->repository()->addFavorite($user->id, $worldHeritage->id);

        $this->assertDatabaseHas('user_favorite', [
            'user_id'                => $user->id,
            'world_heritage_site_id' => $worldHeritage->id,
        ]);
    }

    public function test_addFavorite_throws_exception_when_user_not_found(): void
    {
        $worldHeritage = $this->seedWorldHeritage(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        $this->repository()->addFavorite(999999, $worldHeritage->id);
    }

    public function test_removeFavorite_detaches_world_heritage_site_from_user(): void
    {
        $user          = $this->seedUser();
        $worldHeritage = $this->seedWorldHeritage(1);
        $user->favorites()->attach($worldHeritage->id);

        $this->repository()->removeFavorite($user->id, $worldHeritage->id);

        $this->assertDatabaseMissing('user_favorite', [
            'user_id'                => $user->id,
            'world_heritage_site_id' => $worldHeritage->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_removeFavorite_does_not_throw_when_not_favorited(): void
    {
        $user          = $this->seedUser();
        $worldHeritage = $this->seedWorldHeritage(1);

        $this->repository()->removeFavorite($user->id, $worldHeritage->id);
    }

    public function test_removeFavorite_throws_exception_when_user_not_found(): void
    {
        $worldHeritage = $this->seedWorldHeritage(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        $this->repository()->removeFavorite(999999, $worldHeritage->id);
    }

    public function test_getFavoriteWorldHeritageIds_returns_ids_ordered_by_most_recently_favorited(): void
    {
        $user           = $this->seedUser();
        $worldHeritage1 = $this->seedWorldHeritage(1);
        $worldHeritage2 = $this->seedWorldHeritage(2);

        $user->favorites()->attach($worldHeritage1->id, ['created_at' => now()->subMinute()]);
        $user->favorites()->attach($worldHeritage2->id, ['created_at' => now()]);

        $result = $this->repository()->getFavoriteWorldHeritageIds($user->id);

        $this->assertSame([$worldHeritage2->id, $worldHeritage1->id], $result);
    }

    public function test_getFavoriteWorldHeritageIds_returns_empty_array_when_no_favorites(): void
    {
        $user = $this->seedUser();

        $result = $this->repository()->getFavoriteWorldHeritageIds($user->id);

        $this->assertSame([], $result);
    }

    public function test_getFavoriteWorldHeritageIds_throws_exception_when_user_not_found(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found.');

        $this->repository()->getFavoriteWorldHeritageIds(999999);
    }
}
