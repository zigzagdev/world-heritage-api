<?php

namespace App\Providers;

use Algolia\AlgoliaSearch\Api\SearchClient;
use App\Packages\Domains\Adapter\AlgoliaWorldHeritageSearchAdapter;
use App\Packages\Domains\Ports\WorldHeritageSearchPort;
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
        $this->app->bind(WorldHeritageSearchPort::class, function () {
            $client = SearchClient::create(config('algolia.algolia_app_id'), config('algolia.algolia_search_api_key'));

            return new AlgoliaWorldHeritageSearchAdapter(client: $client, indexName: config('algolia.algolia_index'));
        });
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        Storage::extend('gcs', function ($app, $config) {
            $clientConfig = [];
            if (!empty($config['project_id']))
                $clientConfig['projectId'] = $config['project_id'];
            if (!empty($config['key_file']))
                $clientConfig['keyFilePath'] = $config['key_file'];

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
