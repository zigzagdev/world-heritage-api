<?php

namespace App\Packages\Domains\Infra;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CountryResolver
{
    private const CACHE_KEY = 'country_dict_v1';
    // Cache TTL
    private const TTL_HOURS = 12;

    private function dictionary(): array
    {
        // not to conflict cache even if the environment/connection is different
        $key = self::CACHE_KEY.':'.(string) config('database.default');

        // Cache::remember() is atomic, so it prevents cache stampede when there are multiple concurrent requests and the cache is expired.
        return Cache::store('file')->remember(
            $key,
            now()->addHours(self::TTL_HOURS),
            function () {
                $rows = DB::table('countries')
                    ->select(['state_party_code', 'name_en', 'name_jp'])
                    ->get();

                $dictionary = [];
                $codes = [];

                foreach ($rows as $row) {
                    // ISO3 normalize as Uppercase
                    $code = strtoupper(trim((string) $row->state_party_code));
                    if ($code === '') {
                        continue;
                    }
                    $codes[$code] = true;

                    // English names are normalized by "lowercasing + whitespace normalization" for case-insensitive exact match
                    $en = $this->normalizeEn((string) ($row->name_en ?? ''));
                    if ($en !== '') {
                        $dictionary[$en] = $code;
                    }

                    // Japanese names are normalized by "whitespace normalization" only for exact match
                    $jp = $this->normalizeJp((string) ($row->name_jp ?? ''));
                    if ($jp !== '') {
                        $dictionary[$jp] = $code;
                    }
                }

                return ['dictionary' => $dictionary, 'codes' => $codes];
            }
        );
    }

    public function resolveIso3(string $query): ?string
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $data = $this->dictionary();

        // accept direct ISO3 code input (e.g., FRA)
        if (preg_match('/^[A-Za-z]{3}$/', $query) === 1) {
            $code = strtoupper($query);
            return isset($data['codes'][$code]) ? $code : null;
        }

        // English names in the dictionary are normalized by "lowercasing + whitespace normalization"
        // for case-insensitive exact match, so we do the same normalization to the query before lookup.
        $en = $this->normalizeEn($query);
        if ($en !== '' && isset($data['dictionary'][$en])) {
            return $data['dictionary'][$en];
        }

        // Japanese names in the dictionary are normalized by "whitespace normalization" only for exact match,
        // so we do the same normalization to the query before lookup.
        $jp = $this->normalizeJp($query);
        if ($jp !== '' && isset($data['dictionary'][$jp])) {
            return $data['dictionary'][$jp];
        }

        return null;
    }

    private function normalizeEn(string $searchWord): string
    {
        $searchWord = trim($searchWord);
        if ($searchWord === '') {
            return '';
        }

        $searchWord = preg_replace('/\s+/u', ' ', $searchWord) ?? $searchWord;

        return mb_strtolower($searchWord);
    }

    private function normalizeJp(string $searchWord): string
    {
        $searchWord = trim($searchWord);
        if ($searchWord === '') {
            return '';
        }

        return preg_replace('/\s+/u', ' ', $searchWord) ?? $searchWord;
    }
}