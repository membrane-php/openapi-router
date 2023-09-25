<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use Membrane\OpenAPIRouter\Exception\CannotRouteRequest;

class Router
{
    private int $errorCode = 404;

    public function __construct(
        private readonly RouteCollection $routeCollection
    ) {
    }

    public function route(string $url, string $method): string
    {
        $hostedMatch = $this->routeServer($this->routeCollection->routes['hosted'], $url, $method);
        if ($hostedMatch !== null) {
            return $hostedMatch;
        }

        $hostlessUrl = parse_url($url, PHP_URL_PATH);
        if ($hostlessUrl !== null && $hostlessUrl !== false) {
            $hostlessMatch = $this->routeServer($this->routeCollection->routes['hostless'], $hostlessUrl, $method);
            if ($hostlessMatch !== null) {
                return $hostlessMatch;
            }
        }

        throw CannotRouteRequest::fromErrorCode($this->errorCode);
    }

    /**
     * @param array{
     *                  'static': array<array{
     *                  'static': string[][],
     *                  'dynamic': array{'regex': string, 'paths': string[][]}
     *              }>,
     *              'dynamic': array{
     *                  'regex': string,
     *                  'servers': array<array{
     *                      'static': string[][],
     *                      'dynamic': array{'regex': string, 'paths': string[][]}
     *                  }>
     *              }
     *          } $servers
     */
    private function routeServer(array $servers, string $url, string $method): ?string
    {
        // Check static servers first
        $staticServers = $servers['static'];

        foreach ($staticServers as $staticServer => $paths) {
            if (str_starts_with($url, $staticServer)) {
                $matchingPath = $this->routePath($staticServer, $paths, $url);
                if ($matchingPath !== null) {
                    $matchingOperationId = $this->routeOperation($matchingPath, $method);
                    if ($matchingOperationId !== null) {
                        return $matchingOperationId;
                    }
                }
            }
        }

        // Check dynamic servers second
        $dynamicMatch = $this->findDynamicMatch($servers['dynamic']['regex'], $url);
        if (isset($dynamicMatch['MARK'])) {
            $dynamicServer = $servers['dynamic']['servers'][$dynamicMatch['MARK']];
            $matchingPath = $this->routePath($dynamicMatch[0], $dynamicServer, $url);
            if ($matchingPath !== null) {
                $matchingOperationId = $this->routeOperation($matchingPath, $method);
                if ($matchingOperationId !== null) {
                    return $matchingOperationId;
                }
            }
        }

        return null;
    }

    /** @param array{
     *          'static': array<string,array<string,string>>,
     *          'dynamic': array{'regex': string, 'paths': array<string,array<string,string>>}
     *     } $paths
     * @return string[]
     */
    private function routePath(string $server, array $paths, string $url): ?array
    {
        // Check static paths first
        $path = substr_replace($url, '', 0, strlen($server));

        $matchingPath = $paths['static'][$path] ?? null;
        if ($matchingPath !== null) {
            return $matchingPath;
        }

        // Check dynamic paths second
        $matchingPattern = $this->findDynamicMatch($paths['dynamic']['regex'], $path);
        if (isset($matchingPattern['MARK'])) {
            return $paths['dynamic']['paths'][$matchingPattern['MARK']];
        }

        return null;
    }

    /** @param string[] $path */
    private function routeOperation(array $path, string $method): ?string
    {
        if (isset($path[$method])) {
            return $path[$method];
        }

        $this->errorCode = 405;
        return null;
    }

    /** @return string[] */
    private function findDynamicMatch(string $regex, string $string): array
    {
        preg_match($regex, $string, $matches);
        return $matches;
    }
}
