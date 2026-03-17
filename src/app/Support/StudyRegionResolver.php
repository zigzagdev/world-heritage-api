<?php

namespace App\Support;

use App\Packages\Domains\StudyRegion\CountryAliases;
use App\Packages\Domains\StudyRegion\ExceptionalStudyRegions;
use App\Packages\Domains\StudyRegion\Iso3ToStudyRegionMap;
use App\Packages\Domains\StudyRegion\CountryToStudyRegionMap;
use App\Enums\StudyRegion;

class StudyRegionResolver
{
    public static function resolveFromRecord(
        ?int $siteId,
        ?string $country,
        array $statePartyCodes = []
    ): StudyRegion {
        $regions = self::resolveManyFromRecord($siteId, $country, $statePartyCodes);

        if ($regions === []) {
            return StudyRegion::UNKNOWN;
        }

        if (count($regions) === 1) {
            return $regions[0];
        }

        return StudyRegion::UNKNOWN;
    }

    /**
     * @param array<int, mixed> $statePartyCodes
     * @return array<int, StudyRegion>
     */
    public static function resolveManyFromRecord(
        ?int $siteId,
        ?string $country,
        array $statePartyCodes = []
    ): array {
        $regionFromSiteId = self::resolveFromSiteId($siteId);

        if ($regionFromSiteId !== StudyRegion::UNKNOWN) {
            return [$regionFromSiteId];
        }

        $regionsFromIso3 = self::resolveManyFromIso3List($statePartyCodes);

        if ($regionsFromIso3 !== []) {
            return $regionsFromIso3;
        }

        $regionFromCountry = self::resolveFromCountry($country);

        if ($regionFromCountry !== StudyRegion::UNKNOWN) {
            return [$regionFromCountry];
        }

        return [];
    }

    public static function resolveFromSiteId(?int $siteId): StudyRegion
    {
        if ($siteId === null) {
            return StudyRegion::UNKNOWN;
        }

        return ExceptionalStudyRegions::SITE_ID_TO_REGION[$siteId] ?? StudyRegion::UNKNOWN;
    }

    public static function resolveFromIso3(?string $iso3): StudyRegion
    {
        if ($iso3 === null) {
            return StudyRegion::UNKNOWN;
        }

        $normalized = strtoupper(trim($iso3));

        if ($normalized === '') {
            return StudyRegion::UNKNOWN;
        }

        return Iso3ToStudyRegionMap::all()[$normalized] ?? StudyRegion::UNKNOWN;
    }

    /**
     * @param array<int, mixed> $iso3List
     * @return array<int, StudyRegion>
     */
    public static function resolveManyFromIso3List(array $iso3List): array
    {
        $regions = [];

        foreach ($iso3List as $iso3) {
            if (!is_string($iso3)) {
                continue;
            }

            $region = self::resolveFromIso3($iso3);

            if ($region === StudyRegion::UNKNOWN) {
                continue;
            }

            $regions[$region->value] = $region;
        }

        return array_values($regions);
    }

    public static function resolveFromCountry(?string $country): StudyRegion
    {
        $normalized = self::normalizeCountryName($country);

        if ($normalized === null) {
            return StudyRegion::UNKNOWN;
        }

        return CountryToStudyRegionMap::all()[$normalized] ?? StudyRegion::UNKNOWN;
    }

    public static function normalizeCountryName(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $normalized = trim($country);

        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(
            ['’', '‘', 'ʼ', '`', '´'],
            "'",
            $normalized
        );

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        if (isset(CountryAliases::MAPPING[$normalized])) {
            return CountryAliases::MAPPING[$normalized];
        }

        $withoutTrailingParen = preg_replace('/\s*\([^)]*\)\s*$/u', '', $normalized) ?? $normalized;

        if (isset(CountryAliases::MAPPING[$withoutTrailingParen])) {
            return CountryAliases::MAPPING[$withoutTrailingParen];
        }

        if (isset(CountryToStudyRegionMap::all()[$withoutTrailingParen])) {
            return $withoutTrailingParen;
        }

        if (isset(CountryToStudyRegionMap::all()[$normalized])) {
            return $normalized;
        }

        return $withoutTrailingParen !== '' ? $withoutTrailingParen : null;
    }
}