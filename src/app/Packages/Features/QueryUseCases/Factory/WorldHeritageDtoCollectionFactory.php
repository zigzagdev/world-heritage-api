<?php

namespace App\Packages\Features\QueryUseCases\Factory;

use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;

class WorldHeritageDtoCollectionFactory
{
    public static function build(array $data): WorldHeritageDtoCollection
    {
        $heritages = array_map(function ($heritage) {
            return new WorldHeritageDto(
                id: $heritage['id'],
                unescoId: $heritage['unesco_id'],
                officialName: $heritage['official_name'],
                name: $heritage['name'],
                country: $heritage['country'],
                region: $heritage['region'],
                category: $heritage['category'],
                yearInscribed: $heritage['year_inscribed'],
                latitude: $heritage['latitude'],
                longitude: $heritage['longitude'],
                isEndangered: $heritage['is_endangered'] ?? false,
                nameJp: $heritage['name_jp'] ?? null,
                stateParty: $heritage['state_party'] ?? null,
                criteria: $heritage['criteria'] ?? null,
                areaHectares: $heritage['area_hectares'] ?? null,
                bufferZoneHectares: $heritage['buffer_zone_hectares'] ?? null,
                shortDescription: $heritage['short_description'] ?? null,
                imageUrl: $heritage['image_url'] ?? null,
                unescoSiteUrl: $heritage['unesco_site_url'] ?? null,
                statePartyCodes: $heritage['state_parties'] ?? [],
                statePartiesMeta: $heritage['state_parties_meta'] ?? []
            );
        }, $data);

        return new WorldHeritageDtoCollection(...$heritages);
    }
}