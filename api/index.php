<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    require __DIR__.'/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);

    echo '<h1>Laravel Error</h1>';
    echo '<pre>';
    echo 'Message: '.$e->getMessage()."\n\n";
    echo 'File: '.$e->getFile()."\n";
    echo 'Line: '.$e->getLine()."\n\n";
    echo $e->getTraceAsString();
    echo '</pre>';
}

// require __DIR__.'/../public/index.php';
