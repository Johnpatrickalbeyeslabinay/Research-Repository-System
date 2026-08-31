<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "APP CREATED\n\n";

$manifest = __DIR__ . '/../bootstrap/cache/services.php';

echo "SERVICES MANIFEST EXISTS: ";
var_dump(file_exists($manifest));

if (file_exists($manifest)) {
    echo "\nSERVICES MANIFEST CONTENT:\n";
    $services = require $manifest;

    echo "PROVIDERS:\n";

    foreach ($services['providers'] ?? [] as $provider) {
        echo $provider . "\n";
    }
}
