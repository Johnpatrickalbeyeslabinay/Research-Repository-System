<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

echo "APP CREATED\n";

echo 'VIEW BOUND: ';
var_dump($app->bound('view'));

echo 'VIEW ALIASED: ';
var_dump($app->bound('view'));

if ($app->bound('view')) {
    echo 'VIEW CLASS: ';
    var_dump(get_class($app->make('view')));
}
