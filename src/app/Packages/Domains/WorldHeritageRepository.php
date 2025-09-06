<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Models\Country;
use RuntimeException;
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

    public function updateOneHeritage(
        WorldHeritageEntity $entity
    ): WorldHeritageEntity
    {

        $model = $this->worldHeritage->find($entity->getId());
        if (!$model) {
            throw new RuntimeException('Heritage was not found');
        }

        $update = [
            'official_name'        => $entity->getOfficialName(),
            'name'                 => $entity->getName(),
            'country'              => $entity->getCountry(),
            'region'               => $entity->getRegion(),
            'category'             => $entity->getCategory(),
            'year_inscribed'       => $entity->getYearInscribed(),
            'latitude'             => $entity->getLatitude(),
            'longitude'            => $entity->getLongitude(),
            'is_endangered'        => $entity->isEndangered(),
            'name_jp'              => $entity->getNameJp(),
            'state_party'          => $entity->getStateParty(),
            'criteria'             => $entity->getCriteria(),
            'area_hectares'        => $entity->getAreaHectares(),
            'buffer_zone_hectares' => $entity->getBufferZoneHectares(),
            'short_description'    => $entity->getShortDescription(),
            'image_url'            => $entity->getImageUrl(),
            'unesco_site_url'      => $entity->getUnescoSiteUrl(),
        ];

        $model->fill($update);
        if (!$model->save()) {
            throw new RuntimeException('Failed to update heritage');
        }

        $codes = $entity->getStatePartyCodes();
        $meta  = $entity->getStatePartyMeta() ?? [];

        if (empty($codes) && !empty($meta)) {
            $codes = array_keys($meta);
        }
        if (empty($codes)) {
            $codes = $this->parseStateParty($model->state_party ?? '');
        }

        $codes   = array_values(array_unique(array_map('strtoupper', $codes ?? [])));
        $codeMap = $this->country
            ->whereIn('state_party_code', $codes)
            ->pluck('state_party_code', 'state_party_code')
            ->all();

        if (!empty($codeMap)) {
            if (!empty($meta)) {
                $payload = [];
                foreach ($codes as $code) {
                    if (!isset($codeMap[$code])) continue;
                    $mm = $meta[$code] ?? [];
                    $payload[$codeMap[$code]] = [
                        'is_primary'       => (bool)($mm['is_primary'] ?? false),
                        'inscription_year' => $mm['inscription_year'] ?? null,
                    ];
                }
                $model->countries()->sync($payload);
            } else {
                $model->countries()->sync(array_values($codeMap));
            }
        } else {
            $model->countries()->sync([]);
        }

        $model->state_party = !empty($codes) ? implode(',', $codes) : null;
        $model->save();
        $model->load(['countries' => fn ($q) => $q->withPivot(['is_primary', 'inscription_year'])]);

        $partyMeta = [];
        foreach ($model->countries as $c) {
            $partyMeta[$c->state_party_code] = [
                'is_primary'       => (bool) data_get($c, 'pivot.is_primary', false),
                'inscription_year' => data_get($c, 'pivot.inscription_year'),
            ];
        }

        return new WorldHeritageEntity(
            id:                 $model->id,
            officialName:       $model->official_name,
            name:               $model->name,
            country:            $model->country,
            region:             $model->region,
            category:           $model->category,
            yearInscribed:      $model->year_inscribed,
            latitude:           $model->latitude,
            longitude:          $model->longitude,
            isEndangered:       $model->is_endangered,
            nameJp:             $model->name_jp,
            stateParty:         $model->state_party,
            criteria:           $model->criteria,
            areaHectares:       $model->area_hectares,
            bufferZoneHectares: $model->buffer_zone_hectares,
            shortDescription:   $model->short_description,
            imageUrl:           $model->image_url,
            unescoSiteUrl:      $model->unesco_site_url,
            statePartyCodes:    $this->parseStateParty(
                implode(',', $model->countries->pluck('state_party_code')->all())
            ),
            statePartyMeta:     $partyMeta
        );
    }

    private function parseStateParty(?string $party): array
    {
        if ($party === null || $party === '') return [];

        $parts = array_map('trim', explode(',', $party));
        $parts = array_filter($parts, static fn($v) => $v !== '');
        $parts = array_map('strtoupper', $parts);

        return array_values(array_unique($parts));
    }
 }