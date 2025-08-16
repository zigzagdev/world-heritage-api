<?php

namespace App\Packages\Features\QueryUseCases\Tests;

use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\UseCase\GetWorldHeritageByIdUseCase;
use Mockery;
use Tests\TestCase;
use Database\Seeders\JapaneseWorldHeritageSeeder;
use App\Models\WorldHeritage;
use Illuminate\Support\Facades\DB;

final class GetWorldHeritageByIdUseCaseTest extends TestCase
{
    private $queryService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $this->queryService = app(WorldHeritageQueryService::class);
        $seeder = new JapaneseWorldHeritageSeeder();
        $seeder->run();
    }

    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            WorldHeritage::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private static function arrayData(): array
    {
        return [
            'id' => 1,
            'unesco_id' => '660',
            'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
            'name' => 'Buddhist Monuments in the Horyu-ji Area',
            'name_jp' => '法隆寺地域の仏教建造物',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'year_inscribed' => 1993,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6147,
            'longitude' => 135.7355,
            'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
        ];
    }

    public function test_use_case(): void
    {
        $useCase = new GetWorldHeritageByIdUseCase($this->queryService);

        $result = $useCase->handle(self::arrayData()['id']);

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }
}