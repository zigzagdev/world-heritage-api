<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;

class WorldHeritageDetailFactory
{
    public static function build(array $data): WorldHeritageDto
    {
        $imageCollection = new ImageDtoCollection();
        foreach ((array)($data['images'] ?? []) as $index => $imageData) {
            $imageCollection->add(new ImageDto(
                id: (int)($imageData['id'] ?? 0),
                url: (string)($imageData['url'] ?? ''),
                sortOrder: (int)($imageData['sort_order'] ?? $index),
                width: array_key_exists('width', $imageData) ? (int)$imageData['width'] : null,
                height: array_key_exists('height', $imageData) ? (int)$imageData['height'] : null,
                format: $imageData['format'] ?? null,
                alt: $imageData['alt'] ?? null,
                credit: $imageData['credit'] ?? null,
                isPrimary: (bool)($imageData['is_primary'] ?? ($index === 0)),
                checksum: $imageData['checksum'] ?? null,
            ));
        }

        $criteria = self::normalizeCriteria($data['criteria'] ?? null);
        [$statePartyCodes, $statePartiesMeta] = self::normalizeStateParties($data);

        return new WorldHeritageDto(
            id: (int)$data['id'],
            officialName: (string)($data['official_name'] ?? ''),
            name: (string)($data['name'] ?? ''),
            country: (string)($data['country'] ?? ''),
            region: (string)($data['region'] ?? ''),
            category: (string)($data['category'] ?? ''),
            yearInscribed: (int)($data['year_inscribed'] ?? 0),
            latitude: array_key_exists('latitude', $data) ? (float)$data['latitude'] : null,
            longitude: array_key_exists('longitude', $data) ? (float)$data['longitude'] : null,
            isEndangered: (bool)($data['is_endangered'] ?? false),
            nameJp: $data['name_jp'] ?? null,
            stateParty: $data['state_party'] ?? null,
            criteria: $criteria,
            areaHectares: array_key_exists('area_hectares', $data) ? (float)$data['area_hectares'] : null,
            bufferZoneHectares: array_key_exists('buffer_zone_hectares', $data) ? (float)$data['buffer_zone_hectares'] : null,
            shortDescription: $data['short_description'] ?? null,
            collection: $imageCollection,
            unescoSiteUrl: $data['unesco_site_url'] ?? null,
            statePartyCodes: $statePartyCodes,
            statePartiesMeta: $statePartiesMeta,
            thumbnail: null,
        );
    }

    // ↓ ここより下はそのままでOK（primaryCodeはもう見ない）

    private static function normalizeCriteria(null|array|string $rawCriteria): ?array
    {
        if (is_array($rawCriteria)) {
            return array_values($rawCriteria);
        }

        if (is_string($rawCriteria) && $rawCriteria !== '') {
            $decoded = json_decode($rawCriteria, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }
        }

        return $rawCriteria ? [(string)$rawCriteria] : null;
    }

    private static function normalizeStateParties(array $data): array
    {
        $codeFieldCandidates = [
            'state_party_codes',
            'state_party_code',
            'state_parties',
        ];

        $codes = [];
        foreach ($codeFieldCandidates as $fieldName) {
            $fieldValue = $data[$fieldName] ?? null;

            if (is_string($fieldValue)) {
                $fieldValue = [$fieldValue];
            }

            if (is_array($fieldValue) && !empty($fieldValue)) {
                $codes = $fieldValue;
                break;
            }
        }

        if (empty($codes) && !empty($data['state_party'])) {
            $codes = self::extractIso3CodesFromString((string)$data['state_party']);
        }

        $codes = self::sanitizeIso3Codes($codes);

        $rawMeta = is_array($data['state_parties_meta'] ?? null) ? $data['state_parties_meta'] : [];
        $normalizedMeta = [];

        foreach ($codes as $iso3) {
            $row = $rawMeta[$iso3] ?? [];
            $normalizedMeta[$iso3] = [
                'is_primary' => (bool)($row['is_primary'] ?? false),
                'inscription_year' => array_key_exists('inscription_year', $row)
                    ? (int)$row['inscription_year']
                    : null,
            ];
        }

        return [$codes, $normalizedMeta];
    }

    private static function extractIso3CodesFromString(string $statePartyString): array
    {
        $splits = preg_split('/[;,\s]+/', strtoupper($statePartyString)) ?: [];

        return self::sanitizeIso3Codes($splits);
    }

    private static function sanitizeIso3Codes(array $rawCodes): array
    {
        $normalizedCodes = [];

        foreach ($rawCodes as $rawCode) {
            $iso3 = strtoupper(trim((string)$rawCode));
            if (preg_match('/^[A-Z]{3}$/', $iso3)) {
                if (!in_array($iso3, $normalizedCodes, true)) {
                    $normalizedCodes[] = $iso3;
                }
            }
        }

        return $normalizedCodes;
    }
}
