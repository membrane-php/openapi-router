<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesAPIeceOfCake
{
    public static function getFilePath(): string
    {
        return __DIR__ . '/APIeceOfCake.json';
    }

    public static function getRoutes(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => [
                'static' => [],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/cakes/sponge' => [
                            'get' => ['operationId' => 'findSpongeCakes', 'source' => ''],
                        ]
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/cakes/([^/]+)(*MARK:/cakes/{icing})|/([^/]+)/sponge(*MARK:/{cakeType}/sponge)|/([^/]+)/([^/]+)(*MARK:/{cakeType}/{icing}))$#',
                        'paths' => [
                            '/cakes/{icing}' => [
                                'get' => ['operationId' => 'findCakesByIcing', 'source' => ''],
                                'post' => ['operationId' => 'addCakesByIcing', 'source' => ''],
                            ],
                            '/{cakeType}/sponge' => [
                                'get' => ['operationId' => 'findSpongeByDesserts', 'source' => ''],
                            ],
                            '/{cakeType}/{icing}' => [
                                'get' => ['operationId' => 'findDessertByIcing', 'source' => ''],
                                'post' => ['operationId' => 'addDessertByIcing', 'source' => ''],
                            ],
                        ]
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }

    public static function getRoutesIgnoringServers(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => [
                'static' => [],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/cakes/sponge' => [
                            'get' => ['operationId' => 'findSpongeCakes', 'source' => ''],
                        ]
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/cakes/([^/]+)(*MARK:/cakes/{icing})|/([^/]+)/sponge(*MARK:/{cakeType}/sponge)|/([^/]+)/([^/]+)(*MARK:/{cakeType}/{icing}))$#',
                        'paths' => [
                            '/cakes/{icing}' => [
                                'get' => ['operationId' => 'findCakesByIcing', 'source' => ''],
                                'post' => ['operationId' => 'addCakesByIcing', 'source' => ''],
                            ],
                            '/{cakeType}/sponge' => [
                                'get' => ['operationId' => 'findSpongeByDesserts', 'source' => ''],
                            ],
                            '/{cakeType}/{icing}' => [
                                'get' => ['operationId' => 'findDessertByIcing', 'source' => ''],
                                'post' => ['operationId' => 'addDessertByIcing', 'source' => ''],
                            ],
                        ]
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
