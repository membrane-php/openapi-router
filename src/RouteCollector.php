<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route\Server;

class RouteCollector
{
    public function collect(OpenApi $openApi): RouteCollection
    {
        $collection = $this->collectRoutes($openApi);

        if ($collection === []) {
            throw CannotCollectRoutes::noRoutes();
        }

        return RouteCollection::fromServers(...$collection);
    }

    /** @return array<string, Server> */
    private function collectRoutes(OpenApi $openApi): array
    {
        $collection = [];

        foreach ($openApi->paths as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operationObject) {
                $servers = $this->getServers($openApi, $pathObject, $operationObject);
                foreach ($servers as $serverUrl) {
                    if (!isset($collection[$serverUrl])) {
                        $collection[$serverUrl] = new Server($serverUrl);
                    }
                    $collection[$serverUrl]->addRoute($path, $method, $operationObject->operationId);
                }
            }
        }

        return array_filter($collection, fn($s) => !$s->isEmpty());
    }

    /** @return array<string, string> */
    private function getServers(OpenApi $openAPI, PathItem $path, Operation $operation): array
    {
        if ($operation->servers !== []) {
            $servers = $operation->servers;
        } elseif ($path->servers !== []) {
            $servers = $path->servers;
        } else {
            $servers = $openAPI->servers;
        }

        return array_unique(array_map(fn($p) => rtrim($p->url, '/'), $servers));
    }
}
