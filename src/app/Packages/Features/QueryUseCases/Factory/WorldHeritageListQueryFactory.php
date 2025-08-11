<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;
use DomainException;
use Illuminate\Support\Arr;

class WorldHeritageListQueryFactory
{
    private static array $REQUIRED = [
        'unesco_id',
        'official_name',
        'name',
        'country',
        'category',
        'region',
        'year_inscribed'
    ];

    public static function build(array $request): WorldHeritageListQuery
    {
        self::validation($request);

        return new WorldHeritageListQuery(
            id: Arr::get($request, 'id', null),
            unesco_id: $request['unesco_id'],
            official_name: (string)($request['official_name'] ?? ''),
            name: (string)($request['name'] ?? ''),
            country: (string)($request['country'] ?? ''),
            region: (string)($request['region'] ?? ''),
            category: (string)($request['category'] ?? ''),
            year_inscribed: (int)($request['year_inscribed'] ?? 0),
            latitude: isset($request['latitude'])  ? (float)$request['latitude']  : null,
            longitude: isset($request['longitude']) ? (float)$request['longitude'] : null,
            is_endangered: (bool)($request['is_endangered'] ?? false),
            name_jp: $request['name_jp'] ?? null,
            state_party: $request['state_party'] ?? null,
            criteria: is_string($request['criteria'] ?? null)
                ? json_decode($request['criteria'], true)
                : ($request['criteria'] ?? null),
            area_hectares: isset($request['area_hectares']) ? (float)$request['area_hectares'] : null,
            buffer_zone_hectares: isset($request['buffer_zone_hectares']) ? (float)$request['buffer_zone_hectares'] : null,
            short_description: $request['short_description'] ?? null,
            image_url: $request['image_url'] ?? null,
            unesco_site_url: $request['unesco_site_url'] ?? null,
        );
    }

    private static function validation(array $request): void
    {
        foreach (self::$REQUIRED as $key) {
            if (!array_key_exists($key, $request) || $request[$key] === null) {
                throw new DomainException("{$key} is Required !");
            }
        }
    }
}