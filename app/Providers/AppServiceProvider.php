<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Filesystem;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use Aws\S3\S3Client;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            }
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    $this->app->afterResolving(FilesystemAdapter::class, function (FilesystemAdapter $adapter) {
        if ($adapter->getDriver()->getAdapter() instanceof AwsS3V3Adapter) {
            $adapter->macro('getS3Client', function () {
                $reflection = new \ReflectionClass($this->getDriver()->getAdapter());
                $property = $reflection->getProperty('client');
                $property->setAccessible(true);
                return $property->getValue($this->getDriver()->getAdapter());
            });
        }
    });
}