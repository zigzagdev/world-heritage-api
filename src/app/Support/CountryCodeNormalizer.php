<?php

namespace App\Support;

use InvalidArgumentException;

class CountryCodeNormalizer
{
    public function toIso3List(array $codesOrNames): array
    {
        $alpha2to3 = config('iso3166.alpha2_to_alpha3', []);
        $overrides = config('world_heritage.overrides', []);

        $out = [];
        $seen = [];

        foreach ($codesOrNames as $v) {
            $raw = trim((string) $v);
            if ($raw === '') {
                continue;
            }

            $key = strtoupper($raw);

            if (isset($overrides[$key])) {
                $iso3 = strtoupper((string) $overrides[$key]);
            } elseif (strlen($key) === 2) {
                if (!isset($alpha2to3[$key])) {
                    throw new InvalidArgumentException("Unknown alpha-2 country code: {$key}");
                }
                $iso3 = strtoupper((string) $alpha2to3[$key]);
            } elseif (strlen($key) === 3 && ctype_alpha($key)) {
                $iso3 = $key;
            } else {
                throw new InvalidArgumentException("Unknown country code format: {$raw}");
            }

            if (!isset($seen[$iso3])) {
                $seen[$iso3] = true;
                $out[] = $iso3;
            }
        }

        return $out;
    }
}
