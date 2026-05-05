<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;

class WorldHeritageSummaryFactory
{
    public static function build(array $data): WorldHeritageDto
    {
        $thumbnail = null;

        if (is_array($data['image'] ?? null)) {
            $imageRow = $data['image'];
        } elseif (is_array($data['thumbnail'] ?? null)) {
            $imageRow = $data['thumbnail'];
        } else {
            $imageRow = null;
        }

        $thumbnailId = $data['image_id']
            ?? $data['thumbnail_id']
            ?? ($imageRow['id'] ?? null);

        $thumbnailUrl = $data['thumbnail_url']
            ?? $data['image_url']
            ?? ($imageRow ? self::buildThumbnailUrlFromRow($imageRow) : null);

        if ($thumbnailUrl) {
            $thumbnail = new ImageDto(
                id: (int)($thumbnailId ?? 0),
                url: (string)$thumbnailUrl,
                sortOrder: (int)($data['image_sort_order'] ?? $imageRow['sort_order'] ?? 0),
                isPrimary: (bool)($data['image_is_primary'] ?? $imageRow['is_primary'] ?? true),
            );
        }

        $normalizedCriteria = self::normalizeCriteria($data['criteria'] ?? null);
        [$statePartyCodes, $statePartiesMeta] = self::normalizeStateParties($data);

        return new WorldHeritageDto(
            id: (int)$data['id'],
            officialName: (string)($data['official_name'] ?? ''),
            name: (string)($data['name'] ?? ''),
            country: (string)($data['country'] ?? null),
            countryNameJp: $data['country_name_jp'] ?? null,
            region: (string)($data['region'] ?? ''),
            category: (string)($data['category'] ?? ''),
            yearInscribed: (int)($data['year_inscribed'] ?? 0),
            latitude: array_key_exists('latitude', $data) ? (float)$data['latitude'] : null,
            longitude: array_key_exists('longitude', $data) ? (float)$data['longitude'] : null,
            isEndangered: (bool)($data['is_endangered'] ?? false),
            heritageNameJp: $data['heritage_name_jp'] ?? null,
            stateParty: $data['state_party'] ?? null,
            criteria: $normalizedCriteria,
            areaHectares: array_key_exists('area_hectares', $data) ? (float)$data['area_hectares'] : null,
            bufferZoneHectares: array_key_exists('buffer_zone_hectares', $data) ? (float)$data['buffer_zone_hectares'] : null,
            shortDescription: $data['short_description'] ?? null,
            images: null,
            imageUrl: $thumbnail,
            unescoSiteUrl: $data['unesco_site_url'] ?? null,
            shortDescriptionJp: $data['short_description_jp'] ?? null,
            statePartyCodes: $statePartyCodes,
            statePartiesMeta: $statePartiesMeta,
            mainImageUrl: $data['main_image_url'] ?? null,
        );
    }

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

        $rawCodes = [];
        foreach ($codeFieldCandidates as $fieldName) {
            $value = $data[$fieldName] ?? null;
            if (is_string($value)) {
                $value = [$value];
            }
            if (is_array($value) && $value !== []) {
                $rawCodes = $value;
                break;
            }
        }

        if ($rawCodes === [] && !empty($data['state_party'])) {
            $rawCodes = self::extractIso3CodesFromString((string)$data['state_party']);
        }

        $normalizedCodes = self::sanitizeIso3Codes($rawCodes);
        $inputMeta = is_array($data['state_parties_meta'] ?? null) ? $data['state_parties_meta'] : [];
        $normalizedMeta = [];

        foreach ($normalizedCodes as $iso3) {
            $row = $inputMeta[$iso3] ?? [];
            $normalizedMeta[$iso3] = [
                'is_primary' => (bool)($row['is_primary'] ?? false)
            ];
        }

        return [$normalizedCodes, $normalizedMeta];
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
            if (preg_match('/^[A-Z]{3}$/', $iso3) && !in_array($iso3, $normalizedCodes, true)) {
                $normalizedCodes[] = $iso3;
            }
        }

        return $normalizedCodes;
    }
}
