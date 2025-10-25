<?php

// Import Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set the public path for Vercel
$app->bind('path.public', function() {
    return __DIR__ . '/../public';
});

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);