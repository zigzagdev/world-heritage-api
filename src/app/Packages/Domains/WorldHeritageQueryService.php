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

class WorldHeritageQueryService implements WorldHeritageQueryServiceInterface
{
    public function __construct(
        private readonly WorldHeritage $model,
        private readonly SignedUrlPort $signedUrl
    ) {}

    /**
     * 一覧（最大30件）: サムネのみ、state_party/state_party_code を要件通りに整形
     */
    public function getAllHeritages(): WorldHeritageDtoCollection
    {
        $items = $this->model
            ->select([
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
                'category',
                'criteria',
                'year_inscribed',
                'area_hectares',
                'buffer_zone_hectares',
                'is_endangered',
                'latitude',
                'longitude',
                'short_description',
                'unesco_site_url',
                'thumbnail_image_id',
            ])
            ->with([
                'countries' => function ($countriesQuery) {
                    $countriesQuery
                        ->withPivot(['is_primary', 'inscription_year'])
                        ->orderBy('countries.state_party_code', 'asc');
                },
                'thumbnail' => function ($thumbnailQuery) {
                    $thumbnailQuery->select([
                        'images.id',
                        'images.world_heritage_id',
                        'images.disk',
                        'images.path',
                        'images.width',
                        'images.height',
                        'images.format',
                        'images.checksum',
                        'images.sort_order',
                    ]);
                },
            ])
            ->limit(30)
            ->get();

        $array = $items
            ->map(fn ($heritage) => $this->buildWorldHeritagePayload($heritage))
            ->all();

        return $this->buildDtoFromCollection($array);
    }

    public function getHeritageById(int $id): WorldHeritageDto
    {
        $heritage = $this->model
            ->with([
                'countries' => function ($countriesQuery) {
                    $countriesQuery
                        ->withPivot(['is_primary', 'inscription_year'])
                        ->orderBy('countries.state_party_code', 'asc')
                        ->orderBy('site_state_parties.inscription_year', 'asc');
                },
                'images' => function ($imagesQuery) {
                    $imagesQuery->orderBy('sort_order', 'asc');
                },
                'thumbnail' => function ($thumbnailQuery) {
                    $thumbnailQuery->select([
                        'images.id',
                        'images.world_heritage_id',
                        'images.disk',
                        'images.path',
                        'images.width',
                        'images.height',
                        'images.format',
                        'images.checksum',
                        'images.sort_order',
                        'images.alt',
                        'images.credit',
                    ]);
                },
            ])
            ->findOrFail($id);

        $imageCollection = new ImageDtoCollection();

        foreach (($heritage->images ?? collect()) as $idx => $img) {
            $disk = config('world_heritage.images_disk');
            $url  = $this->signedUrl->forGet($disk, ltrim($img->path, '/'), 300);

            $imageCollection->add(new ImageDto(
                id: $img->id,
                url: $url,
                sortOrder: $img->sort_order,
                width: $img->width,
                height: $img->height,
                format: $img->format,
                alt: $img->alt,
                credit: $img->credit,
                isPrimary: $idx === 0,
                checksum: $img->checksum,
            ));
        }

        $codes = $heritage->countries
            ->pluck('state_party_code')
            ->filter()
            ->map(fn ($code) => strtoupper($code))
            ->unique()
            ->values();

        $statePartyName  = null;
        $statePartyCodes = null;

        if ($codes->count() === 1) {
            $onlyCode     = $codes->first();
            $countryModel = $heritage->countries
                ->first(fn ($country) => strtoupper($country->state_party_code) === $onlyCode);
            $statePartyName  = $countryModel->name ?? null;
            $statePartyCodes = null;
        } elseif ($codes->count() > 1) {
            $statePartyName  = null;
            $statePartyCodes = $codes->all();
        }

        $statePartiesMeta = [];
        foreach ($heritage->countries as $country) {
            $code = strtoupper($country->state_party_code);
            if (!$code) {
                continue;
            }

            $statePartiesMeta[$code] = [
                'is_primary' => (bool) data_get($country, 'pivot.is_primary', false),
                'inscription_year' => data_get($country, 'pivot.inscription_year'),
            ];
        }

        $statePartyCodesCompat = $codes->all();

        return WorldHeritageDetailFactory::build([
            'id' => $heritage->id,
            'official_name' => $heritage->official_name,
            'name' => $heritage->name,
            'country'                => $heritage->country,
            'region'                 => $heritage->region,
            'category'               => $heritage->category,
            'year_inscribed'         => $heritage->year_inscribed,
            'latitude'               => $heritage->latitude,
            'longitude'              => $heritage->longitude,
            'is_endangered'          => (bool) $heritage->is_endangered,
            'name_jp'                => $heritage->name_jp,
            'state_party'            => $statePartyName,
            'criteria'               => $heritage->criteria,
            'area_hectares'          => $heritage->area_hectares,
            'buffer_zone_hectares'   => $heritage->buffer_zone_hectares,
            'short_description'      => $heritage->short_description,
            'images'                 => $imageCollection->toArray(),
            'unesco_site_url'        => $heritage->unesco_site_url,
            'state_party_code'       => $statePartyCodes,
            'state_party_codes'      => $statePartyCodesCompat,
            'state_parties_meta'     => $statePartiesMeta,
        ]);
    }

    public function getHeritagesByIds(
        array $ids,
        int $currentPage,
        int $perPage
    ): PaginationDto {
        $paginator = $this->model
            ->select([
                'id',
                'official_name',
                'name',
                'name_jp',
                'country',
                'region',
                'category',
                'criteria',
                'year_inscribed',
                'area_hectares',
                'buffer_zone_hectares',
                'is_endangered',
                'latitude',
                'longitude',
                'short_description',
                'unesco_site_url',
                'thumbnail_image_id',
            ])
            ->with([
                'countries' => function ($countriesQuery) {
                    $countriesQuery
                        ->withPivot(['is_primary', 'inscription_year'])
                        ->orderBy('countries.state_party_code', 'asc')
                        ->orderBy('site_state_parties.inscription_year', 'asc');
                },
                'thumbnail' => function ($thumbnailQuery) {
                    $thumbnailQuery->select([
                        'images.id',
                        'images.world_heritage_id',
                        'disk',
                        'path',
                        'width',
                        'height',
                        'format',
                        'checksum',
                        'sort_order',
                        'alt',
                        'credit',
                    ]);
                },
            ])
            ->whereIn('id', $ids)
            ->paginate($perPage, ['*'], 'page', $currentPage)
            ->through(function ($heritage) {
                $countries = $heritage->countries ?? collect();

                $codes = $countries->pluck('state_party_code')
                    ->filter()
                    ->map(fn ($code) => strtoupper($code))
                    ->unique()
                    ->values();

                $statePartyName  = null;
                $statePartyCodes = null;
                $stateParties    = [];

                if ($codes->count() === 1) {
                    $onlyCode = $codes->first();
                    $countryModel = $countries
                        ->first(fn ($country) => strtoupper($country->state_party_code) === $onlyCode);
                    $statePartyName = $countryModel->name ?? $heritage->country ?? null;
                    $statePartyCodes = null;
                } elseif ($codes->count() > 1) {
                    $statePartyName  = null;
                    $statePartyCodes = $codes->all();
                    $stateParties    = $codes->all();
                }

                $statePartiesMeta = [];
                foreach ($countries as $country) {
                    $code = strtoupper($country->state_party_code);
                    if (!$code) {
                        continue;
                    }

                    $statePartiesMeta[$code] = [
                        'is_primary'       => (bool) data_get($country, 'pivot.is_primary', false),
                        'inscription_year' => data_get($country, 'pivot.inscription_year'),
                    ];
                }

                $thumbnailModel = $heritage->thumbnail;
                $thumbnailUrl   = $this->buildThumbnailUrl($thumbnailModel);

                return [
                    'id'                     => $heritage->id,
                    'official_name'          => $heritage->official_name,
                    'name'                   => $heritage->name,
                    'name_jp'                => $heritage->name_jp,
                    'country'                => $heritage->country,
                    'region'                 => $heritage->region,
                    'category'               => $heritage->category,
                    'criteria'               => $heritage->criteria,
                    'state_party'            => $statePartyName,
                    'state_party_code'       => $statePartyCodes,
                    'year_inscribed'         => $heritage->year_inscribed,
                    'area_hectares'          => $heritage->area_hectares,
                    'buffer_zone_hectares'   => $heritage->buffer_zone_hectares,
                    'is_endangered'          => (bool) $heritage->is_endangered,
                    'latitude'               => $heritage->latitude,
                    'longitude'              => $heritage->longitude,
                    'short_description'      => $heritage->short_description,
                    'thumbnail_id'           => $thumbnailModel?->id,
                    'thumbnail_url'          => $thumbnailUrl,
                    'unesco_site_url'        => $heritage->unesco_site_url,
                    'state_parties'          => $stateParties,
                    'state_parties_meta'     => $statePartiesMeta,
                ];
            });

        $paginationArray = $paginator->toArray();
        $dtoCollection   = $this->buildDtoFromCollection($paginationArray['data']);

        return new PaginationDto(
            collection: $dtoCollection,
            pagination: collect($paginationArray)->except('data')->toArray()
        );
    }

    private function buildWorldHeritagePayload($heritage): array
    {
        $countryRelations = $heritage->countries ?? collect();

        $statePartyCodeCollection = $countryRelations
            ->pluck('state_party_code')
            ->filter()
            ->map(fn ($code) => strtoupper($code))
            ->unique()
            ->values();

        $statePartyName     = null;
        $statePartyCodeValue = null;
        $statePartyCodeList  = [];

        if ($statePartyCodeCollection->count() === 1) {
            $onlyCode = $statePartyCodeCollection->first();

            $primaryCountry = $countryRelations->first(
                fn ($country) => strtoupper($country->state_party_code) === $onlyCode
            );

            $statePartyName     = $primaryCountry->name ?? $heritage->country ?? null;
            $statePartyCodeValue = null;
        } elseif ($statePartyCodeCollection->count() > 1) {
            $statePartyName      = null;
            $statePartyCodeValue = $statePartyCodeCollection->all();
            $statePartyCodeList  = $statePartyCodeCollection->all();
        }

        $statePartiesMeta = [];
        foreach ($countryRelations as $country) {
            $code = strtoupper($country->state_party_code);
            if (!$code) {
                continue;
            }

            $statePartiesMeta[$code] = [
                'is_primary'       => (bool) ($country->pivot->is_primary ?? false),
                'inscription_year' => $country->pivot->inscription_year ?? null,
            ];
        }

        $thumbnailModel = $heritage->thumbnail;
        $thumbnailUrl   = $this->buildThumbnailUrl($thumbnailModel);

        return [
            'id'                   => $heritage->id,
            'official_name'        => $heritage->official_name,
            'name'                 => $heritage->name,
            'name_jp'              => $heritage->name_jp,
            'country'              => $heritage->country,
            'region'               => $heritage->region,
            'category'             => $heritage->category,
            'criteria'             => $heritage->criteria ?? [],
            'state_party'          => $statePartyName,
            'state_party_code'     => $statePartyCodeValue,
            'year_inscribed'       => $heritage->year_inscribed,
            'area_hectares'        => $heritage->area_hectares,
            'buffer_zone_hectares' => $heritage->buffer_zone_hectares,
            'is_endangered'        => (bool) $heritage->is_endangered,
            'latitude'             => $heritage->latitude,
            'longitude'            => $heritage->longitude,
            'short_description'    => $heritage->short_description,
            'thumbnail_id'         => $thumbnailModel?->id,
            'thumbnail_url'        => $thumbnailUrl,
            'unesco_site_url'      => $heritage->unesco_site_url,
            'state_parties'        => $statePartyCodeList,
            'state_parties_meta'   => $statePartiesMeta,
        ];
    }

    private function buildDtoFromCollection(array $array): WorldHeritageDtoCollection
    {
        return WorldHeritageDtoCollectionFactory::build($array);
    }

    private function buildThumbnailUrl(?object $thumbnailModel): ?string
    {
        if (!$thumbnailModel) {
            return null;
        }

        $diskName = config('world_heritage.images_disk');

        if (!is_string($diskName) || $diskName === '') {
            throw new RuntimeException('world_heritage.images_disk is not configured.');
        }

        $objectPath = ltrim($thumbnailModel->path, '/');

        return $this->signedUrl->forGet($diskName, $objectPath, 300);
    }
}
