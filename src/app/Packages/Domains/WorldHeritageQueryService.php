<?php

namespace App\Packages\Domains;

use App\Common\Pagination\PaginationDto;
use App\Models\WorldHeritage;
use App\Packages\Features\QueryUseCases\Dto\ImageDto;
use App\Packages\Features\QueryUseCases\Dto\ImageDtoCollection;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDtoCollection;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDetailFactory;
use App\Packages\Features\QueryUseCases\QueryServiceInterface\WorldHeritageQueryServiceInterface;
use RuntimeException;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Domains\Ports\SignedUrlPort;
use App\Packages\Features\QueryUseCases\Factory\Dto\WorldHeritageDtoCollectionFactory;
use Illuminate\Support\Facades\Storage;

class WorldHeritageQueryService implements  WorldHeritageQueryServiceInterface
{
    public function __construct(
        private readonly WorldHeritage $model,
        private readonly SignedUrlPort $signedUrl
    ){}

    public function getHeritageById(
        int $id
    ): WorldHeritageDto {

        $heritage = $this->model
            ->with(['countries' => function ($q) {
                $q->withPivot(['is_primary', 'inscription_year'])
                    ->orderBy('countries.state_party_code', 'asc')
                    ->orderBy('site_state_parties.inscription_year', 'asc');
            }])
            ->with(['images' => function ($q) {
                $q->orderBy('sort_order', 'asc');
            }])
            ->findOrFail($id);

        if (!$heritage) {
            throw new RuntimeException("World Heritage was not found.");
        }

        $imageCollection = new ImageDtoCollection();

        foreach (($heritage->images ?? collect()) as $idx => $img) {
            $disk = $img->disk ?? 'gcs';
            $url  = $this->signedUrl->forGet($disk, ltrim($img->path, '/'), 300);

            $imageCollection->add(new ImageDto(
                id:        $img->id,
                url:       $url,
                sortOrder: $img->sort_order,
                width:     $img->width,
                height:    $img->height,
                format:    $img->format,
                alt:       $img->alt,
                credit:    $img->credit,
                isPrimary: $idx === 0,
                checksum:  $img->checksum,
            ));
        }

        $statePartyCodes = $heritage->countries
            ->pluck('state_party_code')
            ->map(fn($code) => strtoupper($code))
            ->all();

        $statePartiesMeta = [];
        foreach ($heritage->countries as $country) {
            $statePartiesMeta[$country->state_party_code] = [
                'is_primary'       => (bool) data_get($country, 'pivot.is_primary', false),
                'inscription_year' => data_get($country, 'pivot.inscription_year'),
            ];
        }

        return WorldHeritageDetailFactory::build([
            'id' => $heritage->id,
            'official_name' => $heritage->official_name,
            'name' => $heritage->name,
            'country' => $heritage->country,
            'region' => $heritage->region,
            'category' => $heritage->category,
            'year_inscribed' => $heritage->year_inscribed,
            'latitude' => $heritage->latitude,
            'longitude' => $heritage->longitude,
            'is_endangered' => (bool) $heritage->is_endangered,
            'name_jp' => $heritage->name_jp,
            'state_party' => $heritage->state_party,
            'criteria' => $heritage->criteria,
            'area_hectares' => $heritage->area_hectares,
            'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
            'short_description' => $heritage->short_description,
            'images' => $imageCollection->toArray(),
            'unesco_site_url' => $heritage->unesco_site_url,
            'state_party_codes' => $statePartyCodes,
            'state_parties_meta' => $statePartiesMeta,
        ]);
    }

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto {

        $paginator = $this->model
            ->with([
                'countries' => function ($q) {
                    $q->withPivot(['is_primary', 'inscription_year'])
                        ->orderBy('countries.state_party_code', 'asc')
                        ->orderBy('site_state_parties.inscription_year', 'asc');
                },
                'getThumbnailImageUrl' => function ($q) {
                    $q->select('id','images.world_heritage_id','disk','path','width','height','format','checksum','sort_order','alt','credit');
                },
            ])
            ->whereIn('id', $ids)
            ->paginate($perPage, ['*'], 'page', $currentPage)
            ->through(function ($heritage) {
                $statePartyCodes = $heritage->countries
                    ->pluck('state_party_code')
                    ->map(fn($c) => strtoupper($c))
                    ->all();

                $statePartiesMeta = [];
                foreach ($heritage->countries as $country) {
                    $statePartiesMeta[$country->state_party_code] = [
                        'is_primary' => (bool) data_get($country, 'pivot.is_primary', false),
                        'inscription_year' => data_get($country, 'pivot.inscription_year'),
                    ];
                }

                $thumb = $heritage->getThumbnailImageUrl;
                $imageUrl = null;
                if ($thumb) {
                    $disk = $thumb->disk ?: config('filesystems.cloud', 'gcs');
                    $path = ltrim($thumb->path, '/');
                    if (in_array($disk, ['gcs', 'gcs_public'], true)) {
                        $imageUrl = $this->signedUrl->forGet($disk, $path, 300);
                    } else {
                        $imageUrl = Storage::disk($disk)->url($path);
                    }
                }

                return [
                    'id' => $heritage->id,
                    'official_name' => $heritage->official_name,
                    'name' => $heritage->name,
                    'name_jp' => $heritage->name_jp,
                    'country' => $heritage->country,
                    'region' => $heritage->region,
                    'category' => $heritage->category,
                    'criteria' => $heritage->criteria,
                    'state_party' => $heritage->state_party,
                    'year_inscribed' => $heritage->year_inscribed,
                    'area_hectares' => $heritage->area_hectares,
                    'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
                    'is_endangered' => (bool) $heritage->is_endangered,
                    'latitude' => $heritage->latitude,
                    'longitude' => $heritage->longitude,
                    'short_description' => $heritage->short_description,
                    'image_url' => $imageUrl,
                    'unesco_site_url' => $heritage->unesco_site_url,
                    'state_parties' => $statePartyCodes,
                    'state_parties_meta' => $statePartiesMeta,
                ];
            });

        $paginationArray = $paginator->toArray();
        $dtoCollection = $this->buildDtoFromCollection($paginationArray['data']);

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: collect($paginationArray)->except('data')->toArray()
        );
    }

    private function buildDtoFromCollection(array $data): WorldHeritageDtoCollection
    {
        return WorldHeritageDtoCollectionFactory::build($data);
    }
}