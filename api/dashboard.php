<?php

// api/dashboard.php - Función serverless para Laravel dashboard
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Create kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle request
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Send response
$response->send();

$kernel->terminate($request, $response);