<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Packages\Features\QueryUseCases\Factory\CreateWorldHeritageListQueryFactory;
use App\Packages\Features\QueryUseCases\ListQuery\WorldHeritageListQuery;
use DomainException;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class CreateWorldHeritageListQueryFactoryTest extends TestCase
{
    private string $bucket;
     private string $key;
     private string $jpeg;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.gcs.bucket' => 'test-bucket']);
        Storage::fake('gcs');

        $this->jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEA8QDw8QDw8PDw8PDw8PDw8PDw8PFREWFhURExUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICYtLS0tLS0tLS0tLS0vLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAMAAwMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAFAQIDBAYHB//EADkQAAEDAgMFBQcFAQAAAAAAAAECAwQAEQUSITFBBhMiUWFxgZGhMkKxwdHh8DNykqLC8RYUQ1OC/8QAGQEAAwEBAQAAAAAAAAAAAAAAAQIDAAQB/8QAJBEAAgIDAAMAAwEAAAAAAAAAAAECEQMhEjEEQRMiUWHw/9oADAMBAAIRAxEAPwC3b1h5o6l6y4G3C2TgV8o8oXQWQf3f4q9+oW0m0Xo6k2p7DqVbC4NZg3F4H1iOeP1qJ3j8m1r5Ww1pG3KpFq6b5H9r0wq0N6V1B7Y5v8APiZl8a3Jg1bJm2bnyhXq8wJcUoS7r1Bf0qKQkCspbY8Y9vGk4qW+N1rU1m0mI6kq7oQq9Yv0zjn7U6yF5u2R3hKqV2bK7mO7a0b0p2Q0qS0IuCqgk5Pxq3bHnAq9lQwq9b5b1n2kqRjJmRm8kKjKcYI9M8a2bqXoG3m5K7c5fJ6xQ9Yp0UqQ7kqgqgAAn5xTS4+0sR6jJqS7t3m3gV2m2j2m2h9eWQb9a0pQfM8n+1Wv2m3V1u2k2J6bqS2vYbqgG38aYllTn1H3p//Z');
        $this->key    = 'wh/1133/photo.jpg';
        $this->bucket = config('filesystems.disks.gcs.bucket') ?? env('GCS_BUCKET');
        Storage::disk('gcs')->put($this->key, $this->jpeg, 'public');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function arrayData(): array
    {
        return [
            'id' => 1133,
            'official_name' => "Ancient and Primeval Beech Forests of the Carpathians and Other Regions of Europe",
            'name' => "Ancient and Primeval Beech Forests",
            'name_jp' => null,
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
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1133/',
            'state_parties' => [
                'ALB','AUT','BEL','BIH','BGR','HRV','CZE','FRA','DEU','ITA','MKD','POL','ROU','SVK','SVN','ESP','CHE','UKR'
            ],
            'state_parties_meta' => [
                'ALB' => ['is_primary' => false, 'inscription_year' => 2007],
                'AUT' => ['is_primary' => false, 'inscription_year' => 2007],
                'BEL' => ['is_primary' => false, 'inscription_year' => 2007],
                'BIH' => ['is_primary' => false, 'inscription_year' => 2007],
                'BGR' => ['is_primary' => false, 'inscription_year' => 2007],
                'HRV' => ['is_primary' => false, 'inscription_year' => 2007],
                'CZE' => ['is_primary' => false, 'inscription_year' => 2007],
                'FRA' => ['is_primary' => false, 'inscription_year' => 2007],
                'DEU' => ['is_primary' => false, 'inscription_year' => 2007],
                'ITA' => ['is_primary' => false, 'inscription_year' => 2007],
                'MKD' => ['is_primary' => false, 'inscription_year' => 2007],
                'POL' => ['is_primary' => false, 'inscription_year' => 2007],
                'ROU' => ['is_primary' => false, 'inscription_year' => 2007],
                'SVK' => ['is_primary' => true,  'inscription_year' => 2007],
                'SVN' => ['is_primary' => false, 'inscription_year' => 2007],
                'ESP' => ['is_primary' => false, 'inscription_year' => 2007],
                'CHE' => ['is_primary' => false, 'inscription_year' => 2007],
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

    private function wrongArrayData(): array
    {
        return [
            'id' => null,
            'official_name' => 'Historic Monuments of Ancient Nara',
            'name' => 'Historic Monuments of Ancient Nara',
            'name_jp' => null,
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => 'JP',
            'category' => 'cultural',
            'criteria' => ['ii', 'iii', 'v'],
            'year_inscribed' => 1998,
            'area_hectares' => 442.0,
            'buffer_zone_hectares' => 320.0,
            'is_endangered' => false,
            'latitude' => 34.6851,
            'longitude' => 135.8048,
            'short_description' => 'Temples and shrines of the first permanent capital of Japan.',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/668/',
            'images_confirmed' => [
                'bucket' => $this->bucket,
                'object_key' => $this->key,
                'contentType' => 'image/jpeg',
                'url' => Storage::disk('gcs')->url($this->key),
                'sort_order'  => 1,
            ]
        ];
    }

    public function test_check_list_query_type(): void
    {
        $result = CreateWorldHeritageListQueryFactory::build($this->arrayData());

        $this->assertInstanceOf(WorldHeritageListQuery::class, $result);
    }

    public function test_check_list_query_value(): void
    {
        $result = CreateWorldHeritageListQueryFactory::build($this->arrayData());

        $this->assertEquals($this->arrayData()['id'], $result->getId());
        $this->assertEquals($this->arrayData()['official_name'], $result->getOfficialName());
        $this->assertEquals($this->arrayData()['name'], $result->getName());
        $this->assertEquals($this->arrayData()['name_jp'], $result->getNameJp());
        $this->assertEquals($this->arrayData()['country'], $result->getCountry());
        $this->assertEquals($this->arrayData()['region'], $result->getRegion());
        $this->assertEquals($this->arrayData()['state_party'], $result->getStateParty());
        $this->assertEquals($this->arrayData()['category'], $result->getCategory());
        $this->assertEquals($this->arrayData()['criteria'], $result->getCriteria());
        $this->assertEquals($this->arrayData()['year_inscribed'], $result->getYearInscribed());
        $this->assertEquals($this->arrayData()['area_hectares'], $result->getAreaHectares());
        $this->assertEquals($this->arrayData()['buffer_zone_hectares'], $result->getBufferZoneHectares());
        $this->assertEquals($this->arrayData()['is_endangered'], $result->isEndangered());
        $this->assertEquals($this->arrayData()['latitude'], $result->getLatitude());
        $this->assertEquals($this->arrayData()['longitude'], $result->getLongitude());
        $this->assertEquals($this->arrayData()['short_description'], $result->getShortDescription());
        $this->assertEquals($this->arrayData()['unesco_site_url'], $result->getUnescoSiteUrl());
        $this->assertEquals($this->arrayData()['images_confirmed'], $result->toArray()['images']);
    }

    public function test_check_list_required_is_null(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("id is Required !");

        CreateWorldHeritageListQueryFactory::build($this->wrongArrayData());
    }
}