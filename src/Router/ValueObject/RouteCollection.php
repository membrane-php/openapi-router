<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Router\ValueObject;

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
}
