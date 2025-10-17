<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;

final class WorldHeritageDtoCollectionFactory
{
    public static function build(array $rows): WorldHeritageDtoCollection
    {
        $dtos = [];

        foreach ($rows as $r) {
            $dtos[] = new WorldHeritageDto(
                id:                 (int)$r['id'],
                officialName:       $r['official_name'] ?? $r['officialName'] ?? '',
                name:               $r['name'],
                country:            $r['country'],
                region:             $r['region'],
                category:           $r['category'],
                yearInscribed:      (int)$r['year_inscribed'],
                latitude:           isset($r['latitude']) ? (float)$r['latitude'] : null,
                longitude:          isset($r['longitude']) ? (float)$r['longitude'] : null,
                isEndangered:       (bool)($r['is_endangered'] ?? false),
                nameJp:             $r['name_jp'] ?? null,
                stateParty:         $r['state_party'] ?? null,
                criteria:           $r['criteria'] ?? null,
                areaHectares:       isset($r['area_hectares']) ? (float)$r['area_hectares'] : null,
                bufferZoneHectares: isset($r['buffer_zone_hectares']) ? (float)$r['buffer_zone_hectares'] : null,
                shortDescription:   $r['short_description'] ?? null,
                collection: isset($r['images']) ? self::makeImageDtos($r['images']) : null,
                unescoSiteUrl: $r['unesco_site_url'] ?? null,
                statePartyCodes: $r['state_parties'] ?? $r['state_party_codes'] ?? [],
                statePartiesMeta: $r['state_parties_meta'] ?? [],
            );
        }
        return new WorldHeritageDtoCollection(...$dtos);
    }

    private static function makeImageDtos(array $images): ImageDtoCollection
    {
        $c = new ImageDtoCollection();
        foreach ($images as $idx => $img) {
            $c->add(new ImageDto(
                id:        $img['id'] ?? null,
                url:       $img['url'] ?? '',
                sortOrder: (int)($img['sort_order'] ?? ($idx + 1)),
                width:     $img['width'] ?? null,
                height:    $img['height'] ?? null,
                format:    $img['format'] ?? null,
                alt:       $img['alt'] ?? null,
                credit:    $img['credit'] ?? null,
                isPrimary: (bool)($img['is_primary'] ?? ($idx === 0)),
                checksum:  $img['checksum'] ?? null,
            ));
        }
        return $c;
    }
}
