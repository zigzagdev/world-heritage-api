<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use Exception;

 readonly class WorldHeritageRepository implements WorldHeritageRepositoryInterface
{
    public function __construct(
        private readonly WorldHeritage $model
    ) {}

    public function insertHeritage(
        WorldHeritageEntity $entity
    ): WorldHeritageEntity {
        $insertValue = [
        'unesco_id' => $entity->getUnescoId(),
        'official_name' => $entity->getOfficialName(),
        'name' => $entity->getName(),
        'country' => $entity->getCountry(),
        'region' => $entity->getRegion(),
        'category' => $entity->getCategory(),
        'year_inscribed' => $entity->getYearInscribed(),
        'latitude' => $entity->getLatitude(),
        'longitude' => $entity->getLongitude(),
        'is_endangered' => $entity->isEndangered(),
        'name_jp' => $entity->getNameJp(),
        'state_party' => $entity->getStateParty(),
        'criteria' => $entity->getCriteria(),
        'area_hectares' => $entity->getAreaHectares(),
        'buffer_zone_hectares' => $entity->getBufferZoneHectares(),
        'short_description' => $entity->getShortDescription(),
        'image_url' => $entity->getImageUrl(),
        'unesco_site_url' => $entity->getUnescoSiteUrl()
        ];

        $heritage = $this->model->create($insertValue);

        if (!$heritage) {
            throw new Exception('Failed to insert heritage');
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

    public function insertHeritages(
        WorldHeritageEntityCollection $collection
    ): WorldHeritageEntityCollection {
        $newCollection = new WorldHeritageEntityCollection();

        foreach ($collection->getAllHeritages() as $entity) {
           $saved = $this->insertHeritage($entity);
           $newCollection->add($saved);
        }

        return $newCollection;
    }
}