<?php

declare(strict_types=1);

include_once __DIR__ . '/../vendor/autoload.php';

use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\Router;

/**
 * @param string[] $requests
 * @param string[] $methods
 */
function benchmark(
    int $iterations,
    Router $router,
    array $requests,
    array $methods = ['get', 'post', 'put', 'delete']
): void {
    $maxRequests = count($requests) - 1;
    $maxMethods = count($methods) - 1;

    $startTime = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        try {
            $router->route($requests[rand(0, $maxRequests)], $methods[rand(0, $maxMethods)]);
        } catch (Exception) {
        }
    }
    $endTime = hrtime(true);

    $timeTaken = ($endTime - $startTime) / 1e+9;
    echo sprintf("Time taken for %d requests: %f seconds\n", $iterations, $timeTaken);
}

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
$fileName = __DIR__ . '/../tests/fixtures/stripe.yaml';
$openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath($fileName);
echo 'read openApi file';
$router = new Router((new RouteCollector())->collect($openApi));

benchmark(10000, $router, $testRequests);
benchmark(1000000, $router, $testRequests);
