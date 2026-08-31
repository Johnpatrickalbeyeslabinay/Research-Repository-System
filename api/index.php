<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "APP CREATED\n\n";

echo "VIEW BOUND: ";
var_dump($app->bound('view'));

echo "\nAPPLICATION PROVIDERS:\n";

$providers = $app->getLoadedProviders();

foreach ($providers as $provider => $loaded) {
    echo $provider . " => " . ($loaded ? 'LOADED' : 'NOT LOADED') . "\n";
}
