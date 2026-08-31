<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

putenv('LOG_CHANNEL=stderr');
putenv('LOG_STACK=stderr');

try {
    require __DIR__.'/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);

    echo '<pre>';
    echo 'ORIGINAL ERROR:'.PHP_EOL;
    echo $e->getMessage().PHP_EOL.PHP_EOL;
    echo 'FILE: '.$e->getFile().PHP_EOL;
    echo 'LINE: '.$e->getLine().PHP_EOL.PHP_EOL;
    echo $e->getTraceAsString();
    echo '</pre>';
}
