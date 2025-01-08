<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use Membrane\OpenAPIReader\ValueObject\Valid\{V30, V31};
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route\Server;

class RouteCollector
{
    public function collect(V30\OpenAPI|V31\OpenAPI $openApi): RouteCollection
    {
        $collection = [];

        foreach ($openApi->paths as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                foreach ($operation->servers as $server) {
                    $collection[$server->url] ??= new Server($server->url);
                    $collection[$server->url]->addRoute(
                        $path,
                        $method,
                        $operation->operationId
                    );
                }
            }
        }

        if ($collection === []) {
            throw CannotCollectRoutes::noRoutes();
        }

        return RouteCollection::fromServers(...$collection);
    }
}
