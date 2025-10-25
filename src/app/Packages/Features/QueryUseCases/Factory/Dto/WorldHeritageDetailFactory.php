<?php

namespace App\Packages\Features\QueryUseCases\Factory\Dto;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;

class WorldHeritageDetailFactory
{
    public static function build(array $data): WorldHeritageDto
    {
        $images = new ImageDtoCollection();
        foreach (($data['images'] ?? []) as $idx => $img) {
            $images->add(new ImageDto(
                id: (int) ($img['id'] ?? 0),
                url: (string) ($img['url'] ?? ''),
                sortOrder: (int) ($img['sort_order'] ?? $idx),
                width: isset($img['width']) ? (int) $img['width']  : null,
                height: isset($img['height']) ? (int) $img['height'] : null,
                format: $img['format'] ?? null,
                alt: $img['alt'] ?? null,
                credit: $img['credit'] ?? null,
                isPrimary: (bool) ($img['is_primary'] ?? ($idx === 0)),
                checksum: $img['checksum'] ?? null,
            ));
        }

        return new WorldHeritageDto(
            id: (int)($data['id']),
            officialName: (string)($data['official_name'] ?? ''),
            name: (string)($data['name'] ?? ''),
            country: (string)($data['country'] ?? ''),
            region:(string)($data['region'] ?? ''),
            category: (string)($data['category'] ?? ''),
            yearInscribed: (int)($data['year_inscribed'] ?? 0),
            latitude: isset($data['latitude'])  ? (float)$data['latitude']  : null,
            longitude: isset($data['longitude']) ? (float)$data['longitude'] : null,
            isEndangered: (bool)($data['is_endangered'] ?? false),
            nameJp: $data['name_jp'] ?? null,
            stateParty: $data['state_party'] ?? null,
            criteria: $data['criteria'] ?? null,
            areaHectares: isset($data['area_hectares']) ? (float)$data['area_hectares'] : null,
            bufferZoneHectares: isset($data['buffer_zone_hectares']) ? (float)$data['buffer_zone_hectares'] : null,
            shortDescription: $data['short_description'] ?? null,
            collection: $images,
            unescoSiteUrl: $data['unesco_site_url'] ?? null,
            statePartyCodes: $data['state_party_codes'] ?? [],
            statePartiesMeta: is_array($data['state_parties_meta'] ?? null) ? $data['state_parties_meta'] : [],
            thumbnail: null
        );
    }
}