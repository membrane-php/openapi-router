<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Router;

use Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection;

class Router
{
    public function __construct(
        private readonly RouteCollection $routeCollection
    ) {
    }

    public function route(string $url, string $method): string
    {
        // Check hosted static servers first
        $hostedStaticServers = $this->routeCollection->routes['hosted']['static'];
        uksort($hostedStaticServers, fn($a, $b) => strlen($a) <=> strlen($b));

        foreach ($hostedStaticServers as $hostedStaticServer => $paths) {
            $matchingOperationId = $this->routePath($hostedStaticServer, $paths, $url, $method);
            if ($matchingOperationId !== null) {
                return $matchingOperationId;
            }
        }

        // Check hosted dynamic servers second
        $hostedDynamicServers = $this->routeCollection->routes['hosted']['dynamic'];
        $hostedDynamicMatch = $this->findDynamicMatch($hostedDynamicServers['regex'], $url);
        var_dump($hostedDynamicMatch);
        if ($hostedDynamicMatch !== null) {
            $hostedDynamicServer = $hostedDynamicServers['servers'][$hostedDynamicMatch];
            $matchingOperationId = $this->routePath($hostedDynamicMatch, $hostedDynamicServer, $url, $method);
            if ($matchingOperationId !== null) {
                return $matchingOperationId;
            }
        }

        // Check hostless static servers third
        $hostedStaticServers = $this->routeCollection->routes['hostless']['static'];
        uksort($hostedStaticServers, fn($a, $b) => strlen($a) <=> strlen($b));

        foreach ($hostedStaticServers as $hostedStaticServer => $paths) {
            $matchingOperationId = $this->routePath($hostedStaticServer, $paths, $url, $method);
            if ($matchingOperationId !== null) {
                return $matchingOperationId;
            }
        }

        // Check hostless dynamic servers fourth
        $hostedDynamicServers = $this->routeCollection->routes['hostless']['dynamic'];
        $hostedDynamicMatch = $this->findDynamicMatch($hostedDynamicServers['regex'], $url);

        if ($hostedDynamicMatch !== null) {
            $hostedDynamicServer = $hostedDynamicServers['servers'][$hostedDynamicMatch];
            $matchingOperationId = $this->routePath($hostedDynamicMatch, $hostedDynamicServer, $url, $method);
            if ($matchingOperationId !== null) {
                return $matchingOperationId;
            }
        }

        throw new \Exception('no matching route found');
    }

    private function findDynamicMatch(string $regex, string $string): ?string
    {
        preg_match($regex, $string, $matches);
        return $matches['MARK'] ?? null;
    }

    /** @param array{
     *          'static': array<string,array<string,string>>,
     *          'dynamic': array{'regex': string, 'paths': array<string,array<string,string>>}
     *     }  $paths
     */
    private function routePath(string $server, array $paths, string $url, string $method): ?string
    {
        // Check static paths first
        if (str_starts_with($url, $server)) {
            $path = substr_replace($url, '', 0, strlen($server));

            $matchingPath = $paths['static'][$path][$method] ?? null;
            if ($matchingPath !== null) {
                return $matchingPath;
            }

            // Check dynamic paths second
            $matchingPattern = $this->findDynamicMatch($paths['dynamic']['regex'], $path);
            if ($matchingPattern !== null && isset($paths['dynamic']['paths'][$matchingPattern][$method])) {
                return $paths['dynamic']['paths'][$matchingPattern][$method];
            }
        }

        return null;
    }
}
