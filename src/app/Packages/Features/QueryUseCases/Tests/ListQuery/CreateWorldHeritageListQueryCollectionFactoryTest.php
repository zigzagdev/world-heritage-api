<?php

namespace App\Packages\Features\QueryUseCases\Tests\ListQuery;

use App\Models\Country;
use App\Models\Image;
use App\Models\WorldHeritage;
use App\Packages\Domains\WorldHeritageEntityCollection;
use App\Packages\Features\QueryUseCases\Factory\ListQuery\CreateWorldHeritageListQueryCollectionFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateWorldHeritageListQueryCollectionFactoryTest extends TestCase
{
    private string $bucket;
    private string $key;
    private string $jpeg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh();

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
            Image::truncate();
            DB::table('site_state_parties')->truncate();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function arrayData(): array
    {
        return [
            [
                'id' => 660,
                'official_name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name' => 'Buddhist Monuments in the Horyu-ji Area',
                'name_jp' => '法隆寺地域の仏教建造物',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.6147,
                'longitude' => 135.7355,
                'short_description' => "Early Buddhist wooden structures including the world's oldest wooden building.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/660/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 661,
                'official_name' => 'Himeji-jo',
                'name' => 'Himeji-jo',
                'name_jp' => '姫路城',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'cultural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 34.8394,
                'longitude' => 134.6939,
                'short_description' => "A masterpiece of Japanese castle architecture in original form.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/661/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 662,
                'official_name' => 'Yakushima',
                'name' => 'Yakushima',
                'name_jp' => '屋久島',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 30.3581,
                'longitude' => 130.546,
                'short_description' => "A subtropical island with ancient cedar forests and diverse ecosystems.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/662/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
            [
                'id' => 663,
                'official_name' => 'Shirakami-Sanchi',
                'name' => 'Shirakami-Sanchi',
                'name_jp' => '白神山地',
                'country' => 'Japan',
                'region' => 'Asia',
                'state_party' => 'JP',
                'category' => 'natural',
                'criteria' => ['ii', 'iii', 'v'],
                'year_inscribed' => 1993,
                'area_hectares' => 442.0,
                'buffer_zone_hectares' => 320.0,
                'is_endangered' => false,
                'latitude' => 40.5167,
                'longitude' => 140.05,
                'short_description' => "Pristine beech forest with minimal human impact.",
                'unesco_site_url' => 'https://whc.unesco.org/en/list/663/',
                'images_confirmed' => [
                    'bucket' => $this->bucket,
                    'object_key' => $this->key,
                    'contentType' => 'image/jpeg',
                    'url' => Storage::disk('gcs')->url($this->key),
                    'sort_order'  => 1,
                ]
            ],
        ];
    }

    public function test_list_query_collection_test_check_type(): void
    {
        $result = CreateWorldHeritageListQueryCollectionFactory::build(self::arrayData());

        $this->assertInstanceOf(WorldHeritageEntityCollection::class, $result);
    }

    public function test_list_query_collection_test_check_value(): void
    {
        $result = CreateWorldHeritageListQueryCollectionFactory::build($this->arrayData());

        foreach ($result->getAllHeritages() as $key => $value) {
            $this->assertEquals($this->arrayData()[$key]['id'], $value->getId());
            $this->assertEquals($this->arrayData()[$key]['official_name'], $value->getOfficialName());
            $this->assertEquals($this->arrayData()[$key]['name'], $value->getName());
            $this->assertEquals($this->arrayData()[$key]['name_jp'], $value->getNameJp());
            $this->assertEquals($this->arrayData()[$key]['country'], $value->getCountry());
            $this->assertEquals($this->arrayData()[$key]['region'], $value->getRegion());
            $this->assertEquals($this->arrayData()[$key]['state_party'], $value->getStateParty());
            $this->assertEquals($this->arrayData()[$key]['category'], $value->getCategory());
            $this->assertEquals($this->arrayData()[$key]['criteria'], $value->getCriteria());
            $this->assertEquals($this->arrayData()[$key]['year_inscribed'], $value->getYearInscribed());
            $this->assertEquals($this->arrayData()[$key]['area_hectares'], $value->getAreaHectares());
            $this->assertEquals($this->arrayData()[$key]['buffer_zone_hectares'], $value->getBufferZoneHectares());
            $this->assertEquals($this->arrayData()[$key]['is_endangered'], $value->isEndangered());
            $this->assertEquals($this->arrayData()[$key]['latitude'], $value->getLatitude());
            $this->assertEquals($this->arrayData()[$key]['longitude'], $value->getLongitude());
            $this->assertEquals($this->arrayData()[$key]['short_description'], $value->getShortDescription());
            $this->assertEquals($this->arrayData()[$key]['unesco_site_url'], $value->getUnescoSiteUrl());
            $this->assertEquals($this->arrayData()[$key]['images_confirmed'], $value->toArray()['images']);
        }
    }
}