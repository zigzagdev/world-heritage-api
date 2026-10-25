<?php

namespace App\Providers;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Packages\Domains\User\Interface\TokenServiceInterface;
use App\Packages\Domains\User\Service\SanctumTokenService;
use App\Packages\Domains\WorldHeritage\Adapter\AlgoliaWorldHeritageSearchAdapter;
use App\Packages\Domains\WorldHeritage\Ports\WorldHeritageSearchPort;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // We bind via a factory closure (instead of a simple class binding) because:
        // - The adapter needs runtime configuration (Algolia app id / API key / index name)
        $this->app->bind(TokenServiceInterface::class, SanctumTokenService::class);

        $this->app->bind(WorldHeritageSearchPort::class, function () {
            $client = SearchClient::create(config('algolia.algolia_app_id'), config('algolia.algolia_search_api_key'));

            return new AlgoliaWorldHeritageSearchAdapter(client: $client, indexName: config('algolia.algolia_index'));
        });

        // Write-key client for index administration (e.g. algolia:sync-index-settings).
        // Resolved lazily via the container so callers only pay for it, and tests
        // can swap it out, when they actually reach a code path that needs it.
        $this->app->bind(SearchClient::class, function () {
            return SearchClient::create(config('algolia.algolia_app_id'), config('algolia.algolia_write_api_key'));
        });
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        Storage::extend('gcs', function ($app, $config) {
            $clientConfig = [];
            if (!empty($config['project_id'])) {
                $clientConfig['projectId'] = $config['project_id'];
            }
            if (!empty($config['key_file'])) {
                $clientConfig['keyFilePath'] = $config['key_file'];
            }

            $storageClient = new StorageClient($clientConfig);
            $bucket = $storageClient->bucket($config['bucket']);
            $prefix = $config['path_prefix'] ?? '';

            $visibility = new UniformBucketLevelAccessVisibility();

            $adapter = new GoogleCloudStorageAdapter($bucket, $prefix, $visibility);
            $filesystem = new Flysystem($adapter);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
