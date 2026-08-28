<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Generator;
use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesPetstoreExpanded
{
    public static function getFilePath(): string
    {
        return __DIR__ . '/docs/petstore-expanded.json';
    }

    public static function getRouteCollection(): RouteCollection
    {
        return new RouteCollection(self::getRoutes());
    }

    public static function getRoutesIgnoringServers(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/pets' => [
                            'get' => ['operationId' => 'findPets', 'source' => ''],
                            'post' => ['operationId' => 'addPet', 'source' => ''],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                        'paths' => [
                            '/pets/{id}' => [
                                'get' => ['operationId' => 'find pet by id', 'source' => ''],
                                'delete' => ['operationId' => 'deletePet', 'source' => ''],
                            ],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }

    public static function getRoutes($source = ''): array
    {
        return [
            'hosted' => [
                'static' => ['http://petstore.swagger.io/api' => [
                    'static' => [
                        '/pets' => [
                            'get' => ['operationId' => 'findPets', 'source' => $source],
                            'post' => ['operationId' => 'addPet', 'source' => $source]
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                        'paths' => [
                            '/pets/{id}' => [
                                'get' => ['operationId' => 'find pet by id', 'source' => $source],
                                'delete' => ['operationId' => 'deletePet', 'source' => $source]
                            ],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
            'hostless' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
        ];
    }
}
