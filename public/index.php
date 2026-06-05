<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detect deployment structure:
// Local: index.php is in public/, Laravel is in ../
// InfinityFree: index.php is in htdocs/, Laravel is in htdocs/laravel/
$baseDir = is_dir(__DIR__.'/laravel') ? __DIR__.'/laravel' : __DIR__.'/..';

if (file_exists($maintenance = $baseDir.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $baseDir.'/vendor/autoload.php';

$app = require_once $baseDir.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
