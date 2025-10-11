<?php

namespace App\Packages\Features\QueryUseCases\Tests\UseCase;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\ImageEntity;
use App\Packages\Domains\ImageEntityCollection;
use App\Packages\Domains\WorldHeritageEntity;
use App\Packages\Domains\WorldHeritageRepositoryInterface;
use App\Packages\Features\QueryUseCases\Dto\WorldHeritageDto;
use App\Packages\Features\QueryUseCases\Factory\UpdateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\UseCase\ImageUploadUseCase;
use App\Packages\Features\QueryUseCases\UseCase\UpdateWorldHeritageUseCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;

class UpdateWorldHeritageUseCaseTest extends TestCase
{
    private string $bucket;
    private string $key;
    private string $jpeg;
    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();
        $seeder = new DatabaseSeeder();
        $seeder->run();
        config(['filesystems.disks.gcs.bucket' => 'test-bucket']);
        Storage::fake('gcs');

        $this->jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEA8QDw8QDw8PDw8PDw8PDw8PDw8PFREWFhURExUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICYtLS0tLS0tLS0tLS0vLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAMAAwMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAFAQIDBAYHB//EADkQAAEDAgMFBQcFAQAAAAAAAAECAwQAEQUSITFBBhMiUWFxgZGhMkKxwdHh8DNykqLC8RYUQ1OC/8QAGQEAAwEBAQAAAAAAAAAAAAAAAQIDAAQB/8QAJBEAAgIDAAMAAwEAAAAAAAAAAAECEQMhEjEEQRMiUWHw/9oADAMBAAIRAxEAPwC3b1h5o6l6y4G3C2TgV8o8oXQWQf3f4q9+oW0m0Xo6k2p7DqVbC4NZg3F4H1iOeP1qJ3j8m1r5Ww1pG3KpFq6b5H9r0wq0N6V1B7Y5v8APiZl8a3Jg1bJm2bnyhXq8wJcUoS7r1Bf0qKQkCspbY8Y9vGk4qW+N1rU1m0mI6kq7oQq9Yv0zjn7U6yF5u2R3hKqV2bK7mO7a0b0p2Q0qS0IuCqgk5Pxq3bHnAq9lQwq9b5b1n2kqRjJmRm8kKjKcYI9M8a2bqXoG3m5K7c5fJ6xQ9Yp0UqQ7kqgqgAAn5xTS4+0sR6jJqS7t3m3gV2m2j2m2h9eWQb9a0pQfM8n+1Wv2m3V1u2k2J6bqS2vYbqgG38aYllTn1H3p//Z');
        $this->key    = 'wh/1133/photo.jpg';
        $this->bucket = config('filesystems.disks.gcs.bucket') ?? env('GCS_BUCKET');
        Storage::disk('gcs')->put($this->key, $this->jpeg, 'public');
    }
    protected function tearDown(): void
    {
        $this->refresh();
        parent::tearDown();
    }

    private function refresh(): void
    {
        if (env('APP_ENV') === 'testing') {
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
             WorldHeritage::truncate();
             Country::truncate();
             DB::table('site_state_parties')->truncate();
             Image::truncate();
             DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => "カルパティア山脈とヨーロッパ各地の古代及び原生ブナ林。",
            'country' => 'Slovakia',
            'region' => 'Europe',
            'category' => 'natural',
            'criteria' => ['ix'],
            'state_party' => null,
            'year_inscribed' => 2007,
            'area_hectares' => 99947.81,
            'buffer_zone_hectares' => 296275.8,
            'is_endangered' => false,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'short_description' => 'Transnational serial property of European beech forests illustrating post-glacial expansion and ecological processes across Europe.',
            'image_url' => '',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
            'state_parties' => [
                'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
            ],
            'state_parties_meta' => [
                'ALB' => ['is_primary' => false, 'inscription_year' => 2017],
                'AUT' => ['is_primary' => false, 'inscription_year' => 2017],
                'BEL' => ['is_primary' => false, 'inscription_year' => 2017],
                'BIH' => ['is_primary' => false, 'inscription_year' => 2021],
                'BGR' => ['is_primary' => false, 'inscription_year' => 2017],
                'HRV' => ['is_primary' => false, 'inscription_year' => 2017],
                'CZE' => ['is_primary' => false, 'inscription_year' => 2021],
                'FRA' => ['is_primary' => false, 'inscription_year' => 2021],
                'DEU' => ['is_primary' => false, 'inscription_year' => 2011],
                'ITA' => ['is_primary' => false, 'inscription_year' => 2017],
                'MKD' => ['is_primary' => false, 'inscription_year' => 2021],
                'POL' => ['is_primary' => false, 'inscription_year' => 2021],
                'ROU' => ['is_primary' => false, 'inscription_year' => 2017],
                'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                'SVN' => ['is_primary' => false, 'inscription_year' => 2017],
                'ESP' => ['is_primary' => false, 'inscription_year' => 2017],
                'CHE' => ['is_primary' => false, 'inscription_year' => 2021],
                'UKR' => ['is_primary' => false, 'inscription_year' => 2007],
            ],
            'images_confirmed' => [
                'bucket' => $this->bucket,
                'object_key' => $this->key,
                'contentType' => 'image/jpeg',
                'url' => Storage::disk('gcs')->url($this->key),
                'sort_order'  => 1,
            ]
        ];
    }

    private function mockListQuery(): WorldHeritageListQuery
    {
        $factory = Mockery::mock(
            'alias' . UpdateWorldHeritageListQueryFactory::class
        );

        $mock = Mockery::mock(WorldHeritageListQuery::class);

        $factory
            ->shouldReceive('build')
            ->andReturn($mock);

        $mock
            ->shouldReceive('getId')
            ->andReturn($this->arrayData()['id']);

        $mock
            ->shouldReceive('getOfficialName')
            ->andReturn($this->arrayData()['official_name']);

        $mock
            ->shouldReceive('getName')
            ->andReturn($this->arrayData()['name']);

        $mock
            ->shouldReceive('getNameJp')
            ->andReturn($this->arrayData()['name_jp']);

        $mock
            ->shouldReceive('getCountry')
            ->andReturn($this->arrayData()['country']);

        $mock
            ->shouldReceive('getRegion')
            ->andReturn($this->arrayData()['region']);

        $mock
            ->shouldReceive('getCategory')
            ->andReturn($this->arrayData()['category']);

        $mock
            ->shouldReceive('getCriteria')
            ->andReturn($this->arrayData()['criteria']);

        $mock
            ->shouldReceive('getYearInscribed')
            ->andReturn($this->arrayData()['year_inscribed']);

        $mock
            ->shouldReceive('getAreaHectares')
            ->andReturn($this->arrayData()['area_hectares']);

        $mock
            ->shouldReceive('getBufferZoneHectares')
            ->andReturn($this->arrayData()['buffer_zone_hectares']);

        $mock
            ->shouldReceive('isEndangered')
            ->andReturn($this->arrayData()['is_endangered']);

        $mock
            ->shouldReceive('getLatitude')
            ->andReturn($this->arrayData()['latitude']);

        $mock
            ->shouldReceive('getLongitude')
            ->andReturn($this->arrayData()['longitude']);

        $mock
            ->shouldReceive('getShortDescription')
            ->andReturn($this->arrayData()['short_description']);

        $mock
            ->shouldReceive('getUnescoSiteUrl')
            ->andReturn($this->arrayData()['unesco_site_url']);

        $mock
            ->shouldReceive('getStatePartyCodes')
            ->andReturn($this->arrayData()['state_parties']);

        $mock
            ->shouldReceive('getStateParty')
            ->andReturn($this->arrayData()['state_party']);

        $mock
            ->shouldReceive('getStatePartiesMeta')
            ->andReturn($this->arrayData()['state_parties_meta']);

        $mock
            ->shouldReceive('getStatePartyCodesOrFallback')
            ->andReturn($this->arrayData()['state_parties_meta']);

        return $mock;
    }

    private function mockRepository()
    {
        $repository = Mockery::mock(WorldHeritageRepositoryInterface
        ::class);

        $repository
            ->shouldReceive('updateOneHeritage')
            ->with(Mockery::type(WorldHeritageEntity::class))
            ->andReturn(new WorldHeritageEntity(
                $this->arrayData()['id'],
                $this->arrayData()['official_name'],
                $this->arrayData()['name'],
                $this->arrayData()['country'],
                $this->arrayData()['region'],
                $this->arrayData()['category'],
                $this->arrayData()['year_inscribed'],
                $this->arrayData()['latitude'],
                $this->arrayData()['longitude'],
                $this->arrayData()['is_endangered'],
                $this->arrayData()['name_jp'],
                $this->arrayData()['state_party'],
                $this->arrayData()['criteria'],
                $this->arrayData()['area_hectares'],
                $this->arrayData()['buffer_zone_hectares'],
                $this->arrayData()['short_description'],
                $this->mockImageEntity(),
                $this->arrayData()['unesco_site_url'],
                $this->arrayData()['state_parties'],
                $this->arrayData()['state_parties_meta']
            ));

        return $repository;
    }

    private function mockRequest(): Request
    {
        $mock = Mockery::mock(Request::class);

        $mock
            ->shouldReceive('all')
            ->andReturn($this->arrayData());

        $mock->shouldReceive('offsetExists')
            ->with('images_confirmed')->andReturn(true);
        $mock->shouldReceive('offsetGet')
            ->with('images_confirmed')->andReturn($this->arrayData()['images_confirmed']);

        return $mock;
    }

    public function test_use_case_check_type(): void
    {
        $useCase = new UpdateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle($this->mockListQuery());

        $this->assertInstanceOf(WorldHeritageDto::class, $result);
    }

    private static function mockImage(): array
    {
        return [
            'id' => null,
            'world_heritage_id' => null,
            'disk' => 'gcs',
            'path' => 'heritages/1133/001.jpg',
            'width' => null,
            'height' => null,
            'format' => 'jpg',
            'checksum' => null,
            'sort_order' => 1,
            'alt' => 'front',
            'credit' => 'me',
        ];
    }

    private function mockImageEntity(): ImageEntityCollection
    {
        $entity = new ImageEntity(
            $this->mockImage()['id'],
            $this->mockImage()['world_heritage_id'],
            $this->mockImage()['disk'],
            $this->mockImage()['path'],
            $this->mockImage()['width'],
            $this->mockImage()['height'],
            $this->mockImage()['format'],
            $this->mockImage()['checksum'],
            $this->mockImage()['sort_order'],
            $this->mockImage()['alt'],
            $this->mockImage()['credit']
        );

        return new ImageEntityCollection($entity);
    }

    private function mockImageUploadUseCase(): ImageUploadUseCase
    {
        $useCase = Mockery::mock(ImageUploadUseCase::class);

        $useCase->shouldReceive('buildImageCollectionAfterPut')
            ->with($this->arrayData()['images_confirmed'])
            ->andReturn($this->mockImageEntity());

        return $useCase;
    }

    public function test_use_case_check_value(): void
    {
        $useCase = new UpdateWorldHeritageUseCase(
            $this->mockRepository(),
            $this->mockImageUploadUseCase()
        );

        $result = $useCase->handle($this->mockListQuery());

        $this->assertSame($this->arrayData()['id'], $result->getId());
        $this->assertSame($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertSame($this->arrayData()['name'], $result->getName());
        $this->assertSame($this->arrayData()['name_jp'], $result->getNameJp());
        $this->assertSame($this->arrayData()['country'], $result->getCountry());
        $this->assertSame($this->arrayData()['region'], $result->getRegion());
        $this->assertSame($this->arrayData()['category'], $result->getCategory());
        $this->assertSame($this->arrayData()['criteria'], $result->getCriteria());
        $this->assertSame($this->arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertSame($this->arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertSame($this->arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertSame($this->arrayData()['is_endangered'], $result->isEndangered());
        $this->assertSame($this->arrayData()['latitude'], $result->getLatitude());
        $this->assertSame($this->arrayData()['longitude'], $result->getLongitude());
        $this->assertSame($this->arrayData()['short_description'], $result->getShortDescription());
        $this->assertSame($this->arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertSame($this->arrayData()['state_parties'], $result->getStatePartyCodes());
        $this->assertSame($this->arrayData()['state_parties_meta'], $result->getStatePartiesMeta());
        $this->assertNotNull($result->getImages());
    }
}