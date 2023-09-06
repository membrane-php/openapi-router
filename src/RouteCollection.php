<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

use Membrane\OpenAPIRouter\Route\Server;

class RouteCollection
{
    /**
     * @param  array{
     *              'hosted' : array{
     *                  'static': array<array{
     *                      'static': string[][],
     *                      'dynamic': array{'regex': string, 'paths': string[][]}
     *                  }>,
     *                  'dynamic': array{
     *                      'regex': string,
     *                      'servers': array<array{
     *                          'static': string[][],
     *                          'dynamic': array{'regex': string, 'paths': string[][]}
     *                      }>
     *                  }
     *              },
     *              'hostless' : array{
     *                  'static': array<array{
     *                      'static': string[][],
     *                      'dynamic': array{'regex': string, 'paths': string[][]}
     *                  }>,
     *                  'dynamic': array{
     *                      'regex': string,
     *                      'servers': array<array{
     *                          'static': string[][],
     *                          'dynamic': array{'regex': string, 'paths': string[][]}
     *                      }>
     *                  }
     *              }
     *          } $routes
     */
    public function __construct(
        public readonly array $routes
    ) {
    }

    public static function fromServers(Server ...$servers): self
    {
        $filteredServers = array_filter($servers, fn($s) => !$s->isEmpty());
        usort($filteredServers, fn($a, $b) => $a->howManyDynamicComponents() <=> $b->howManyDynamicComponents());

        $hostedServers = $hostlessServers = [];
        foreach ($filteredServers as $server) {
            if ($server->isHosted()) {
                $hostedServers[$server->url] = $server;
            } else {
                $hostlessServers[$server->url] = $server;
            }
        }

        $hostedStaticServers = $hostedDynamicServers = $hostedGroupRegex = [];
        foreach ($hostedServers as $server) {
            if ($server->isDynamic()) {
                $hostedDynamicServers[$server->url] = $server->jsonSerialize();
                $hostedGroupRegex[] = sprintf('%s(*MARK:%s)', $server->regex, $server->url);
            } else {
                $hostedStaticServers[$server->url] = $server->jsonSerialize();
            }
        }

        $hostlessStaticServers = $hostlessDynamicServers = $hostlessGroupRegex = [];
        foreach ($hostlessServers as $server) {
            if ($server->isDynamic()) {
                $hostlessDynamicServers[$server->url] = $server->jsonSerialize();
                $hostlessGroupRegex[] = sprintf('%s(*MARK:%s)', $server->regex, $server->url);
            } else {
                $hostlessStaticServers[$server->url] = $server->jsonSerialize();
            }
        }

        return new self([
            'hosted' => [
                'static' => $hostedStaticServers,
                'dynamic' => [
                    'regex' => sprintf('#^(?|%s)#', implode('|', $hostedGroupRegex)),
                    'servers' => $hostedDynamicServers,
                ],
            ],
            'hostless' => [
                'static' => $hostlessStaticServers,
                'dynamic' => [
                    'regex' => sprintf('#^(?|%s)#', implode('|', $hostlessGroupRegex)),
                    'servers' => $hostlessDynamicServers,
                ],
            ],
        ]);
    }
}
