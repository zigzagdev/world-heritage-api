<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Models\Country;
 readonly class WorldHeritageRepository implements WorldHeritageRepositoryInterface
{
    public function __construct(
        private readonly WorldHeritage $worldHeritage,
        private readonly Country $country
    ) {}

    public function insertHeritage(
        WorldHeritageEntity $entity
    ): WorldHeritageEntity {

        $insertValue = [
        'id' => $entity->getId(),
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
        'criteria' => $entity->getCriteria(),
        'area_hectares' => $entity->getAreaHectares(),
        'buffer_zone_hectares' => $entity->getBufferZoneHectares(),
        'short_description' => $entity->getShortDescription(),
        'image_url' => $entity->getImageUrl(),
        'unesco_site_url' => $entity->getUnescoSiteUrl()
        ];

        $heritage = $this->worldHeritage->create($insertValue);

        $meta  = $entity->getStatePartyMeta() ?? [];
        $codes = $entity->getStatePartyCodes();
        if (empty($codes) && !empty($meta)) {
            $codes = array_keys($meta);
        }
        if (empty($codes)) {
            $codes = $entity->getStatePartyCodesOrFallback();
        }

        $codes = array_values(array_unique(array_map('strtoupper', $codes)));
        $codeIds = $this->country
            ->whereIn('state_party_code', $codes)
            ->pluck('state_party_code', 'state_party_code')
            ->all();

        if (!empty($codeIds)) {
            if (!empty($meta)) {
                $payload = [];
                foreach ($codes as $code) {
                    if (!isset($codeIds[$code])) {
                        continue;
                    }
                    $m = $meta[$code] ?? [];
                    $payload[$codeIds[$code]] = [
                        'is_primary'       => (bool)($m['is_primary'] ?? false),
                        'inscription_year' => $m['inscription_year'] ?? null,
                    ];
                }
                $heritage->countries()->sync($payload);
            } else {
                $heritage->countries()->sync(array_values($codeIds));
            }
        } else {
            $heritage->countries()->sync([]);
        }

        $heritage->state_party = !empty($codes) ? implode(',', $codes) : null;

        $heritage->load(['countries' => function ($q) {
            $q->withPivot(['is_primary', 'inscription_year']);
        }]);

        $partyMeta = [];
        foreach ($heritage->countries as $country) {
            $partyMeta[$country->state_party_code] = [
                'is_primary'       => (bool) data_get($country, 'pivot.is_primary', false),
                'inscription_year' => data_get($country, 'pivot.inscription_year'),
            ];
        }

        return new WorldHeritageEntity(
            id: $heritage->id,
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
            criteria: $heritage->criteria,
            areaHectares: $heritage->area_hectares,
            bufferZoneHectares: $heritage->buffer_zone_hectares,
            shortDescription: $heritage->short_description,
            imageUrl: $heritage->image_url,
            unescoSiteUrl: $heritage->unesco_site_url,
            statePartyCodes: $this->parseStateParty(
                implode(',', $heritage->countries->pluck('state_party_code')->all())
            ),
            statePartyMeta: $partyMeta
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

//    public function updateOneHeritage(
//        WorldHeritageEntity $entity
//    ): WorldHeritageEntity
//    {
//        $targetEntity = $this->model->find($entity->getUnescoId());
//
//        if (!$targetEntity) {
//            throw new Exception('Heritage was not found');
//        }
//
//        $updateValue = [
//            'id' => $entity->getId(),
//            'unesco_id' => $entity->getUnescoId(),
//            'official_name' => $entity->getOfficialName(),
//            'name' => $entity->getName(),
//            'country' => $entity->getCountry(),
//            'region' => $entity->getRegion(),
//            'category' => $entity->getCategory(),
//            'year_inscribed' => $entity->getYearInscribed(),
//            'latitude' => $entity->getLatitude(),
//            'longitude' => $entity->getLongitude(),
//            'is_endangered' => $entity->isEndangered(),
//            'name_jp' => $entity->getNameJp(),
//            'state_party' => $entity->getStateParty(),
//            'criteria' => $entity->getCriteria(),
//            'area_hectares' => $entity->getAreaHectares(),
//            'buffer_zone_hectares' => $entity->getBufferZoneHectares(),
//            'short_description' => $entity->getShortDescription(),
//            'image_url' => $entity->getImageUrl(),
//            'unesco_site_url' => $entity->getUnescoSiteUrl()
//        ];
//
//        $codes = method_exists($entity, 'getStatePartyCodes')
//            ? (array) $entity->getStatePartyCodes()
//            : $this->parseStatePartyString((string) $entity->getStateParty());
//
//        $updatedHeritage = $this->model->updateOrFail(
//          $updateValue
//        );
//
//        if (!$updatedHeritage) {
//            throw new Exception('Failed to update heritage');
//        }
//
//        return new WorldHeritageEntity(
//            id: $updateValue['id'],
//            unescoId: $updateValue['unesco_id'],
//            officialName: $updateValue['official_name'],
//            name: $updateValue['name'],
//            country: $updateValue['country'],
//            region: $updateValue['region'],
//            category: $updateValue['category'],
//            yearInscribed: $updateValue['year_inscribed'],
//            latitude: $updateValue['latitude'],
//            longitude: $updateValue['longitude'],
//            isEndangered: $updateValue['is_endangered'],
//            nameJp: $updateValue['name_jp'],
//            stateParty: $updateValue['state_party'],
//            criteria: $updateValue['criteria'],
//            areaHectares: $updateValue['area_hectares'],
//            bufferZoneHectares: $updateValue['buffer_zone_hectares'],
//            shortDescription: $updateValue['short_description'],
//            imageUrl: $updateValue['image_url'],
//            unescoSiteUrl: $updateValue['unesco_site_url']
//        );
//    }

    private function parseStateParty(?string $party): array
    {
        if ($party === null || $party === '') return [];

        $parts = array_map('trim', explode(',', $party));
        $parts = array_filter($parts, static fn($v) => $v !== '');
        $parts = array_map('strtoupper', $parts);

        return array_values(array_unique($parts));
    }
 }