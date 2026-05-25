<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Container;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Config\Repository;

$app = new Container();
Facade::setFacadeApplication($app);

// Minimal config matching your production values exactly
$config = new Repository([
    'filesystems' => [
        'default' => 'b2',
        'disks' => [
            'b2' => [
                'driver' => 's3',
                'key'    => '005b583483830f00000000007',
                'secret' => 'K005EC1cOkfS32m0XbjYrqobE12Juus',
                'region' => 'us-east-005',
                'bucket' => 'laravel-bucket',
                'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
                'use_path_style_endpoint' => true,
                'visibility' => 'private',
                'throw' => true,
            ],
        ],
    ],
]);

$app->instance('config', $config);

// Bind filesystem manager
$app->singleton('filesystem', function ($app) {
    return new FilesystemManager($app);
});

try {
    echo "Generating URL...\n";
    
    // Manually create the driver to ensure it works
    $disk = $app->make('filesystem')->disk('b2');
    
    $url = $disk->temporaryUrl(
        'profile-pictures/profile_116_1765026989.png', 
        now()->addMinutes(30)
    );

    echo "GENERATED URL:\n$url\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
