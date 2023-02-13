<?php

declare(strict_types=1);

include_once __DIR__ . '/../vendor/autoload.php';

use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\Router;

$testRequests = [
    'http://weirder.co.uk/and/blink',
    'http://weirder.co.uk/and/harley',
    'http://wonderful.io/xor',
    'http://wonderful.io/and/xor',
    '/xor',
    '/v1/xor',
    'http://weirdest.com/however',
    'http://weirder.co.uk/however',
    'http://weird.io/and/or',
    'http://weird.io/therefore/however',
];
$testMethods = ['get', 'post', 'put', 'delete'];

$openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath(__DIR__ . '/../tests/fixtures/stripe.yaml');
$routeCollection = (new RouteCollector())->collect($openApi);
$router = new Router($routeCollection);

$startTime = hrtime(true);
for ($i = 0; $i < 1000000; $i++) {
    try {
        $router->route($testRequests[rand(0, 9)], $testMethods[rand(0, 3)]);
    } catch (Exception) {
    }
}
$endTime = hrtime(true);

$timeTaken = ($endTime - $startTime) / 1e+9;
echo sprintf("Time Taken for 10000 requests: %f seconds\n", $timeTaken);
