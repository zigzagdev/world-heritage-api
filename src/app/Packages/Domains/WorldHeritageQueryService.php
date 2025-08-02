<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use RuntimeException;

class WorldHeritageQueryService implements  WorldHeritageQueryServiceInterface
{
    public function __construct(
        private readonly WorldHeritage $model
    ){}

    public function getHeritageById(
        int $id
    ): WorldHeritageEntity {
        $heritage = $this->model->findOrFail($id);

        if (!$heritage) {
            throw new RuntimeException("World Heritage was not found.");
        }

        return new WorldHeritageEntity(
            id: $heritage->id,
            unescoId: $heritage->unesco_id,
            officialName: $heritage->official_name,
            name: $heritage->name,
            country: $heritage->country,
            region: $heritage->region,
            category: $heritage->category,
            yearInscribed: $heritage->year_inscribed,
            latitude: $heritage->latitude,
            longitude: $heritage->longitude,
            isEndangered: $heritage->is_endangered,
            nameJp: $heritage->name_jp,
            stateParty: $heritage->state_party,
            criteria: $heritage->criteria,
            areaHectares: $heritage->area_hectares,
            bufferZoneHectares: $heritage->buffer_zone_hectares,
            shortDescription: $heritage->short_description,
            imageUrl: $heritage->image_url,
            unescoSiteUrl: $heritage->unesco_site_url
        );
    }
}