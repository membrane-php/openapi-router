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

    public static function getRoutes(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => [
                'static' => ['http://petstore.swagger.io/api' => [
                    'static' => [
                        '/pets' => ['get' => 'findPets', 'post' => 'addPet'],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                        'paths' => [
                            '/pets/{id}' => ['get' => 'find pet by id', 'delete' => 'deletePet'],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
            'hostless' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
        ]);
    }

    public static function getRoutesIgnoringServers(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/pets' => ['get' => 'findPets', 'post' => 'addPet'],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                        'paths' => [
                            '/pets/{id}' => ['get' => 'find pet by id', 'delete' => 'deletePet'],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
