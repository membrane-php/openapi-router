<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use Membrane\OpenAPIReader\ValueObject\Valid\V30\OpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route\Server;

class RouteCollector
{
    public function collect(OpenAPI $openApi): RouteCollection
    {
        $collection = [];

        foreach ($openApi->paths as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                foreach ($operation->servers as $server) {
                    $url = rtrim($server->url, '/');
                    if (!isset($collection[$url])) {
                        $collection[$url] = new Server($url);
                    }
                    $collection[$url]->addRoute($path, $method, $operation->operationId);
                }
            }
        }

        if ($collection === []) {
            throw CannotCollectRoutes::noRoutes();
        }

        return RouteCollection::fromServers(...$collection);
    }
}
