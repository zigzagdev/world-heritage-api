<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Google\Cloud\Storage\StorageClient;
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
        //
    }

    /**
     * Bootstrap any application services.
     */

public function boot(): void
{
    Storage::extend('gcs', function ($app, $config) {
        $clientConfig = [];
        if (!empty($config['project_id']))  $clientConfig['projectId']   = $config['project_id'];
        if (!empty($config['key_file']))    $clientConfig['keyFilePath'] = $config['key_file'];

        $storageClient = new StorageClient($clientConfig);
        $bucket = $storageClient->bucket($config['bucket']);
        $prefix = $config['path_prefix'] ?? '';

        $visibility = new UniformBucketLevelAccessVisibility();

        $adapter    = new GoogleCloudStorageAdapter($bucket, $prefix, $visibility);
        $filesystem = new Flysystem($adapter);

        return new FilesystemAdapter($filesystem, $adapter, $config);
    });
}

}
