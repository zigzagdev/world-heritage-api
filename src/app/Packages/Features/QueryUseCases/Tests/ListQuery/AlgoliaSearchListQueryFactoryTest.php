<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Enums\StudyRegion;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\AlgoliaSearchListQueryFactory;
use App\Packages\Features\QueryUseCases\ListQuery\AlgoliaSearchListQuery;
use InvalidArgumentException;
use Tests\TestCase;

class AlgoliaSearchListQueryFactoryTest extends TestCase
{
    public function test_check_list_query_type(): void
    {
        $query = AlgoliaSearchListQueryFactory::build(
            keyword: 'test',
            countryName: 'Japan',
            countryIso3: 'JPN',
            region: 'Asia',
            category: 'Natural',
            yearFrom: 1978,
            yearTo: 2000,
            currentPage: 1,
            perPage: 30,
        );

        $this->assertInstanceOf(AlgoliaSearchListQuery::class, $query);
        $this->assertInstanceOf(StudyRegion::class, $query->region);
    }

    public function test_check_list_query_value(): void
    {
        $query = AlgoliaSearchListQueryFactory::build(
            keyword: 'test',
            countryName: 'Japan',
            countryIso3: 'JPN',
            region: 'Asia',
            category: 'Natural',
            yearFrom: 1978,
            yearTo: 2000,
            currentPage: 1,
            perPage: 30,
        );

        $this->assertSame('test', $query->keyword);
        $this->assertSame('Japan', $query->countryName);
        $this->assertSame('JPN', $query->countryIso3);
        $this->assertSame(StudyRegion::ASIA, $query->region);
        $this->assertSame('Natural', $query->category);
        $this->assertSame(1978, $query->yearFrom);
        $this->assertSame(2000, $query->yearTo);
        $this->assertSame(1, $query->currentPage);
        $this->assertSame(30, $query->perPage);
    }

    public function test_check_nullable_params(): void
    {
        $query = AlgoliaSearchListQueryFactory::build(
            keyword: null,
            countryName: null,
            countryIso3: null,
            region: null,
            category: null,
            yearFrom: null,
            yearTo: null,
            currentPage: 1,
            perPage: 30,
        );

        $this->assertNull($query->keyword);
        $this->assertNull($query->countryName);
        $this->assertNull($query->countryIso3);
        $this->assertNull($query->region);
        $this->assertNull($query->category);
        $this->assertNull($query->yearFrom);
        $this->assertNull($query->yearTo);
    }

    public function test_check_invalid_region_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid region value: invalid_region');

        AlgoliaSearchListQueryFactory::build(
            keyword: null,
            countryName: null,
            countryIso3: null,
            region: 'invalid_region',
            category: null,
            yearFrom: null,
            yearTo: null,
            currentPage: 1,
            perPage: 30,
        );
    }

    public function test_all_study_regions_are_valid(): void
    {
        foreach (StudyRegion::cases() as $case) {
            $query = AlgoliaSearchListQueryFactory::build(
                keyword: null,
                countryName: null,
                countryIso3: null,
                region: $case->value,
                category: null,
                yearFrom: null,
                yearTo: null,
                currentPage: 1,
                perPage: 30,
            );

            $this->assertSame($case, $query->region);
        }
    }
}