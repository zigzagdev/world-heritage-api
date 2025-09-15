<?php

namespace App\Packages\Domains;

use App\Models\WorldHeritage;
use App\Models\Country;
use Carbon\Carbon;
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

        if ($entity->getImageCollection() !== null) {
            foreach ($entity->getImageCollection()->getItems() as $image) {
                $imageRows[] = ([
                    'world_heritage_id' => $heritage->id,
                    'disk'       => $image->getDisk(),
                    'path'       => $image->getPath(),
                    'width'      => $image->getWidth(),
                    'height'     => $image->getHeight(),
                    'format'     => $image->getFormat(),
                    'checksum'   => $image->getChecksum(),
                    'sort_order' => $image->getSortOrder(),
                    'alt'        => $image->getAlt(),
                    'credit'     => $image->getCredit(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            if(!empty($imageRows)) {
                $heritage->images()->insert($imageRows);
            }
        }
        $heritage->load([
            'countries' => fn($q) => $q->withPivot(['is_primary','inscription_year']),
            'images',
        ]);

        $images = [];
        foreach ($heritage->images as $m) {
            $images[] = new ImageEntity(
                id:        $m->id,
                disk:      $m->disk,
                path:      $m->path,
                width:     $m->width,
                height:    $m->height,
                format:    $m->format,
                checksum:  $m->checksum,
                sortOrder: $m->sort_order,
                alt:       $m->alt,
                credit:    $m->credit,
            );
        }

        $imageCollection = new ImageEntityCollection(...$images);

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
            stateParty: $heritage->state_party,
            criteria: $heritage->criteria,
            areaHectares: $heritage->area_hectares,
            bufferZoneHectares: $heritage->buffer_zone_hectares,
            shortDescription: $heritage->short_description,
            collection: $imageCollection,
            unescoSiteUrl: $heritage->unesco_site_url,
            statePartyCodes: $this->parseStateParty(
                implode(',', $heritage->countries->pluck('state_party_code')->all())
            ),
            statePartyMeta: $partyMeta,
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

         // 本体の更新
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

         $meta  = $entity->getStatePartyMeta() ?? [];

         $codes = $entity->getStatePartyCodes();
         $codes = array_values(array_unique(array_map('strtoupper', $codes)));


         $payload = [];
         foreach ($codes as $code) {
             $m = $meta[$code] ?? [];
             $payload[$code] = [
                 'is_primary' => (bool)($m['is_primary'] ?? false),
                 'inscription_year' => $m['inscription_year'] ?? null,
             ];
         }
         $model->countries()->sync($payload);

         $primary = null;
         foreach ($codes as $c) {
             if (!empty($meta[$c]['is_primary'])) {
                 $primary = $c;
                 break;
             }
         }
         $primary ??= ($codes[0] ?? null);
         $model->state_party = $primary ?: null;
         $model->save();

         $model->load(['countries' => fn ($q) => $q->withPivot(['is_primary','inscription_year'])]);

         $partyMeta = [];
         foreach ($model->countries as $c) {
             $partyMeta[$c->state_party_code] = [
                 'is_primary'       => (bool)data_get($c, 'pivot.is_primary', false),
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
             statePartyMeta: $partyMeta
         );
     }

     public function updateManyHeritages(
         WorldHeritageEntityCollection $collection
     ): WorldHeritageEntityCollection {
         $newCollection = new WorldHeritageEntityCollection();

         foreach ($collection->getAllHeritages() as $entity) {
             $saved = $this->updateOneHeritage($entity);
             $newCollection->add($saved);
         }

         return $newCollection;
     }

    public function deleteOneHeritage(int $id): void
    {
        $heritageModel = $this->worldHeritage->find($id);
        if (!$heritageModel) {
            throw new RuntimeException('Heritage was not found');
        }
        $heritageModel->countries()->detach();

        if (!$heritageModel->delete()) {
            throw new RuntimeException('Failed to delete heritage');
        }
    }

    public function deleteManyHeritages(array $ids): void
    {
        $this->worldHeritage
            ->whereIn('id', $ids)
            ->each(function ($heritage) {
                $heritage->countries()->detach();
                $heritage->delete();
            });
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