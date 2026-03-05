<?php

namespace App\Packages\Domains\Test\QueryService;

use App\Common\Pagination\PaginationDto;
use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Packages\Domains\WorldHeritageQueryService;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
use App\Packages\Domains\Ports\Dto\HeritageSearchResult;


class WorldHeritageQueryService_getAllHeritagesTest extends TestCase
{

    private $queryService;

    private const CURRENT_PAGE = 1;
    private const PER_PAGE = 10;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();

        $this->app->bind(WorldHeritageSearchPort::class, function () {
            return new class implements WorldHeritageSearchPort {
                public function search($query, int $currentPage, int $perPage): HeritageSearchResult {
                    return new HeritageSearchResult(ids: [], total: 0, currentPage: 1, perPage: $perPage, lastPage: 0);
                }
            };
        });

        $this->queryService = app(WorldHeritageQueryService::class);
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
            Country::truncate();
            DB::table('site_state_parties')->truncate();
            Image::truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function test_fetch_data_check_type(): void
    {
        $result = $this->queryService->getAllHeritages(
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $this->assertInstanceOf(PaginationDto::class, $result);
    }

    public function test_fetch_data_check_value(): void
    {
        $result = $this->queryService->getAllHeritages(
            self::CURRENT_PAGE,
            self::PER_PAGE
        );

        $arrayResult = $result->toArray();

        $this->assertArrayHasKey('items', $arrayResult);
        $this->assertArrayHasKey('pagination', $arrayResult);

        $this->assertIsArray($arrayResult['items']);
        $this->assertIsArray($arrayResult['pagination']);

        $this->assertSame([
            'current_page',
            'per_page',
            'total',
            'last_page',
            'from',
            'to',
            'path',
            'first_page_url',
            'last_page_url',
            'next_page_url',
            'prev_page_url',
            'links'
        ], array_keys($arrayResult['pagination']));
    }
}