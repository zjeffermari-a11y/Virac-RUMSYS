<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Container;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Config\Repository;

// Bootstrap a minimal Laravel-like environment for Facades
$app = new Container();
Facade::setFacadeApplication($app);

$config = new Repository([
    'filesystems' => [
        'default' => 'b2',
        'disks' => [
            'b2' => [
                'driver' => 's3',
                // HARDCODED CREDENTIALS we verified with CLI
                'key'    => '005b583483830f00000000007',
                'secret' => 'K005EC1cOkfS32m0XbjYrqobE12Juus',
                'region' => 'us-east-005',
                'bucket' => 'laravel-bucket',
                'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
                'use_path_style_endpoint' => true,
                'visibility' => 'private',
            ],
        ],
    ],
]);

$app->instance('config', $config);

// Bind the filesystem manager
$app->bind('filesystem', function ($app) {
    return new FilesystemManager($app);
});

try {
    // 1. Create a dummy file (Uncomment if needed, but we assume file exists or we just test signature)
    // Storage::disk('b2')->put('test_signature.txt', 'Hello World');
    
    // 2. Generate detailed debug info
    echo "Generating URL for: laravel-bucket/test_signature.txt\n";
    echo "Using Endpoint: https://s3.us-east-005.backblazeb2.com\n";
    echo "Using Key ID: 005b583483830f00000000007\n\n";

    // 3. Generate Signed URL
    $url = Storage::disk('b2')->temporaryUrl(
        'test_signature.txt',
        now()->addMinutes(30)
    );

    echo "GENERATED SIGNED URL:\n";
    echo $url . "\n\n";
    
    echo "Please copy the URL above and paste it into your browser.\n";
    echo "If this works, the problem is your Cloud Environment variables (likely hidden spaces).\n";
    echo "If this FAILS, the problem is our config logic/library version.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
