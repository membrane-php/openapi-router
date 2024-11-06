<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Generator;
use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesApiAndRoutes
{
    private const PETSTORE_EXPANDED = __DIR__ . '/docs/petstore-expanded.json';
    private const WEIRD_AND_WONDERFUL = __DIR__ . '/WeirdAndWonderful.json';

    public static function defaultBehaviour(): Generator
    {
        yield 'petstore expanded' => [self::PETSTORE_EXPANDED, new RouteCollection([
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
        ])];

        yield 'weird and wonderful' => [self::WEIRD_AND_WONDERFUL, new RouteCollection([
            'hosted' => [
                'static' => [
                    'http://weirdest.com' => [
                        'static' => [
                            '/however' => ['put' => 'put-however', 'post' => 'post-however'],
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                            'paths' => [
                                '/and/{name}' => ['get' => 'get-and']
                            ],
                        ],
                    ],
                    'http://weirder.co.uk' => [
                        'static' => [
                            '/however' => ['get' => 'get-however']
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                            'paths' => [
                                '/and/{name}' => ['put' => 'put-and', 'post' => 'post-and'],
                            ],
                        ],
                    ],
                    'http://wonderful.io' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                    'http://wonderful.io/and' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|)$#',
                            'paths' => [],
                        ],
                    ],
                    'http://wonderful.io/or' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                ],
                'dynamic' => [
                    'regex' => '#^(?|http://weird.io/([^/]+)(*MARK:http://weird.io/{conjunction}))#',
                    'servers' => ['http://weird.io/{conjunction}' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ]],
                ],
            ],
            'hostless' => [
                'static' => [
                    '' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                    '/v1' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                ],
                'dynamic' => [
                    'regex' => '#^(?|/([^/]+)(*MARK:/{version}))#',
                    'servers' => ['/{version}' => [
                        'static' => [
                            '/or' => ['post' => 'post-or'],
                            '/xor' => ['delete' => 'delete-xor'],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ]],
                ],
            ],
        ])];
    }

    public static function ignoringServers(): Generator
    {
        yield 'petstore-expanded, ignoring servers' => [self::PETSTORE_EXPANDED, new RouteCollection([
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
        ])];

        yield 'weird-and-wonderful, ignoring servers' => [self::WEIRD_AND_WONDERFUL, new RouteCollection([
            'hosted' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/or' => [
                            'post' => 'post-or',
                        ],
                        '/xor' => [
                            'delete' => 'delete-xor',
                        ],
                        '/however' => [
                            'get' => 'get-however',
                            'put' => 'put-however',
                            'post' => 'post-however',
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                        'paths' => [
                            '/and/{name}' => [
                                'get' => 'get-and',
                                'put' => 'put-and',
                                'post' => 'post-and',
                            ],
                        ]
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ])];
    }
}
