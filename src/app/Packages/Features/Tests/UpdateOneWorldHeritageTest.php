<?php

namespace App\Packages\Features\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\WorldHeritage;
use Database\Seeders\DatabaseSeeder;
use App\Packages\Domains\Ports\SignedUrlPort;
use App\Packages\Domains\Ports\ObjectStoragePort;

class UpdateOneWorldHeritageTest extends TestCase
{
    use RefreshDatabase;

    private string $key;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        config(['filesystems.disks.gcs.bucket' => 'my-test-bucket']);
        Storage::fake('gcs');

        $this->key = 'test-image.jpg';
        Storage::disk('gcs')->put($this->key, 'binary');

        $this->app->bind(ObjectStoragePort::class, function () {
            return new class implements ObjectStoragePort {
                public function exists(string $disk, string $key): bool
                { return Storage::disk($disk)->exists($key); }
                public function put(string $disk, string $key, string $mime, $payload): void
                { Storage::disk($disk)->put($key, $payload); }
                public function delete(string $disk, string $key): void
                { Storage::disk($disk)->delete($key); }
            };
        });

        $this->app->bind(SignedUrlPort::class, function () {
            return new class implements SignedUrlPort {
                public function forPut(string $disk, string $key, string $mime, int $ttlSec = 300): string
                { return "https://example.test/put/{$disk}/" . rawurlencode($key); }
                public function forGet(string $disk, string $key, int $ttlSec = 300): string
                { return "https://example.test/get/{$disk}/" . rawurlencode($key); }
            };
        });
    }

    private function payload(): array
    {
        return [
            'id' => 1418,
            'official_name' => 'Fujisan, sacred place and source of artistic inspiration',
            'name' => 'Fujisan',
            'name_jp' => '富士山—信仰の対象と芸術の源泉(更新をした。)',
            'country' => 'Japan',
            'region' => 'Asia',
            'state_party' => null,
            'category' => 'Cultural',
            'criteria' => ['iii', 'vi'],
            'year_inscribed' => 2013,
            'area_hectares' => 122334.0,
            'buffer_zone_hectares' => 0.0,
            'is_endangered' => false,
            'latitude' => 35.3606,
            'longitude' => 138.7274,
            'short_description' => '...',
            'unesco_site_url' => 'https://whc.unesco.org/en/list/1418/',
            'state_parties' => ['JPN'],
            'state_parties_meta' => [
                'JPN' => ['is_primary' => true, 'inscription_year' => 2013],
            ],
            'images_confirmed' => [[
                'object_key'   => $this->key,
                'content_type' => 'image/jpeg',
                'sort_order'   => 1,
            ]],
        ];
    }

    public function test_feature(): void
    {
        $id = 1418;

        $initial = WorldHeritage::find($id);
        $this->assertNotNull($initial, 'Missing world heritage id=1418 from seeder');
        $before = $initial->name_jp;

        $res = $this->putJson("/api/v1/heritages/{$id}", $this->payload());

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'id','official_name','name','name_jp','country','region','state_party',
                'category','criteria','year_inscribed','area_hectares','buffer_zone_hectares',
                'is_endangered','latitude','longitude','short_description','unesco_site_url',
                'state_party_codes',
                'state_parties_meta' => ['JPN' => ['is_primary','inscription_year']],
            ],
        ]);

        $this->assertNotSame($before, $res->json('data.name_jp'));
    }
}
