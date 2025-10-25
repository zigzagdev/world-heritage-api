<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;

class WorldHeritageSummaryFactory
{
    public static function build(array $data): WorldHeritageDto
    {
        $thumbnail = null;
        $imageArr  = is_array($data['image'] ?? null) ? $data['image'] : null;

        $imageId = $data['image_id']
            ?? $data['thumbnail_id']
            ?? ($imageArr['id'] ?? null);

        $imageUrl = $data['thumbnail_url']
            ?? $data['image_url']
            ?? ($imageArr['url'] ?? null);

        if (!empty($imageUrl)) {
            $thumbnail = new ImageDto(
                id: (int) $imageId,
                url: (string) $imageUrl,
                sortOrder: (int) ($data['image_sort_order'] ?? $imageArr['sort_order'] ?? 0),
                width: isset($data['image_width']) ? (int)$data['image_width'] : ($imageArr['width']  ?? null),
                height: isset($data['image_height']) ? (int)$data['image_height'] : ($imageArr['height'] ?? null),
                format: $data['image_format'] ?? ($imageArr['format'] ?? null),
                alt: $data['image_alt'] ?? ($imageArr['alt'] ?? ($data['name'] ?? null)),
                credit: $data['image_credit'] ?? ($imageArr['credit'] ?? null),
                isPrimary: (bool) ($data['image_is_primary'] ?? $imageArr['is_primary'] ?? true),
                checksum: $data['image_checksum'] ?? ($imageArr['checksum'] ?? null),
            );
        }

        return new WorldHeritageDto(
            id: (int) ($data['id']),
            officialName: (string) ($data['official_name'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            country: (string) ($data['country'] ?? ''),
            region: (string) ($data['region'] ?? ''),
            category: (string) ($data['category'] ?? ''),
            yearInscribed: (int) ($data['year_inscribed'] ?? 0),
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            isEndangered: (bool) ($data['is_endangered'] ?? false),
            nameJp: $data['name_jp'] ?? null,
            stateParty: $data['state_party'] ?? null,
            criteria: $data['criteria'] ?? null,
            areaHectares: isset($data['area_hectares']) ? (float) $data['area_hectares'] : null,
            bufferZoneHectares: isset($data['buffer_zone_hectares']) ? (float) $data['buffer_zone_hectares'] : null,
            shortDescription: $data['short_description'] ?? null,
            collection: null,
            unescoSiteUrl: $data['unesco_site_url'] ?? null,
            statePartyCodes: $data['state_parties'] ?? [],
            statePartiesMeta: is_array($data['state_parties_meta'] ?? null) ? $data['state_parties_meta'] : [],
            thumbnail: $thumbnail
        );
    }
}
