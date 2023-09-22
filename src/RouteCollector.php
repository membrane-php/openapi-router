<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route\Route;
use Membrane\OpenAPIRouter\Route\Server as ServerRoute;

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

    /** @return array<string, ServerRoute> */
    private function collectRoutes(OpenApi $openApi): array
    {
        $collection = [];

        $rootServers = $this->getServers($openApi);
        foreach ($rootServers as $url => $regex) {
            $collection[$url] ??= new ServerRoute($url, $regex);
        }

        $operationIds = [];
        foreach ($openApi->paths as $path => $pathObject) {
            $pathRegex = $this->getRegex($path);

            $pathServers = $this->getServers($pathObject);
            foreach ($pathServers as $url => $regex) {
                $collection[$url] ??= new ServerRoute($url, $regex);
            }

            foreach ($pathObject->getOperations() as $method => $operationObject) {
                $operationServers = $this->getServers($operationObject);
                foreach ($operationServers as $url => $regex) {
                    $collection[$url] ??= new ServerRoute($url, $regex);
                }

                if ($operationServers !== []) {
                    $servers = $operationServers;
                } elseif ($pathServers !== []) {
                    $servers = $pathServers;
                } else {
                    $servers = $rootServers;
                }

                foreach ($servers as $url => $regex) {
                    $collection[$url]->addRoute(new Route($path, $pathRegex, $method, $operationObject->operationId));
                }

                $operationIds[$operationObject->operationId] = ['path' => $path, 'operation' => $method];
            }
        }

        return array_filter($collection, fn($s) => !$s->isEmpty());
    }

    /** @return array<string, string> */
    private function getServers(OpenApi|PathItem|Operation $object): array
    {
        $uniqueServers = array_unique(array_map(fn($p) => rtrim($p->url, '/'), $object->servers));
        return array_combine($uniqueServers, array_map(fn($p) => $this->getRegex($p), $uniqueServers));
    }

    private function getRegex(string $path): string
    {
        $regex = preg_replace('#{[^/]+}#', '([^/]+)', $path);
        assert($regex !== null); // The pattern is hardcoded, valid regex so should not cause an error in preg_replace

        return $regex;
    }
}