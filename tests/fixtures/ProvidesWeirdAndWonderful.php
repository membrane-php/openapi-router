<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Generator;
use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesWeirdAndWonderful
{
    public static function getFilePath(): string
    {
        return __DIR__ . '/WeirdAndWonderful.json';
    }

    public static function getRoutes(): RouteCollection
    {
        return new RouteCollection([
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
        ]);
    }

    public static function getRoutesIgnoringServers(): RouteCollection
    {
        return new RouteCollection([
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
        ]);
    }
}
