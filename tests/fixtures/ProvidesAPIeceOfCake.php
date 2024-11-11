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
                            'get' => 'findSpongeCakes',
                        ]
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/cakes/([^/]+)(*MARK:/cakes/{icing})|/([^/]+)/sponge(*MARK:/{cakeType}/sponge)|/([^/]+)/([^/]+)(*MARK:/{cakeType}/{icing}))$#',
                        'paths' => [
                            '/cakes/{icing}' => [
                                'get' => 'findCakesByIcing',
                                'post' => 'addCakesByIcing',
                            ],
                            '/{cakeType}/sponge' => [
                                'get' => 'findSpongeByDesserts',
                            ],
                            '/{cakeType}/{icing}' => [
                                'get' => 'findDessertByIcing',
                                'post' => 'addDessertByIcing',
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
                            'get' => 'findSpongeCakes',
                        ]
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/cakes/([^/]+)(*MARK:/cakes/{icing})|/([^/]+)/sponge(*MARK:/{cakeType}/sponge)|/([^/]+)/([^/]+)(*MARK:/{cakeType}/{icing}))$#',
                        'paths' => [
                            '/cakes/{icing}' => [
                                'get' => 'findCakesByIcing',
                                'post' => 'addCakesByIcing',
                            ],
                            '/{cakeType}/sponge' => [
                                'get' => 'findSpongeByDesserts',
                            ],
                            '/{cakeType}/{icing}' => [
                                'get' => 'findDessertByIcing',
                                'post' => 'addDessertByIcing',
                            ],
                        ]
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
