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
                            '/however' => [
                                'put' => ['operationId' => 'put-however', 'source' => ''],
                                'post' => ['operationId' => 'post-however', 'source' => ''],
                            ],
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                            'paths' => [
                                '/and/{name}' => [
                                    'get' => ['operationId' => 'get-and', 'source' => '']
                                ]
                            ],
                        ],
                    ],
                    'http://weirder.co.uk' => [
                        'static' => [
                            '/however' => [
                                'get' => ['operationId' => 'get-however', 'source' => '']
                            ]
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                            'paths' => [
                                '/and/{name}' => [
                                    'put' => ['operationId' => 'put-and', 'source' => ''],
                                    'post' => ['operationId' => 'post-and', 'source' => '']
                                ],
                            ],
                        ],
                    ],
                    'http://wonderful.io' => [
                        'static' => [
                            '/or' => [
                                'post' => ['operationId' => 'post-or', 'source' => '']
                            ],
                            '/xor' => [
                                'delete' => ['operationId' => 'delete-xor', 'source' => '']
                            ],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                    'http://wonderful.io/and' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|)$#',
                            'paths' => [],
                        ],
                    ],
                    'http://wonderful.io/or' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                ],
                'dynamic' => [
                    'regex' => '#^(?|http://weird.io/([^/]+)(*MARK:http://weird.io/{conjunction}))#',
                    'servers' => ['http://weird.io/{conjunction}' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ]],
                ],
            ],
            'hostless' => [
                'static' => [
                    '' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                    '/v1' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
                        ],
                        'dynamic' => ['regex' => '#^(?|)$#', 'paths' => []],
                    ],
                ],
                'dynamic' => [
                    'regex' => '#^(?|/([^/]+)(*MARK:/{version}))#',
                    'servers' => ['/{version}' => [
                        'static' => [
                            '/or' => ['post' => ['operationId' => 'post-or', 'source' => '']],
                            '/xor' => ['delete' => ['operationId' => 'delete-xor', 'source' => '']],
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
                            'post' => ['operationId' => 'post-or', 'source' => ''],
                        ],
                        '/xor' => [
                            'delete' => ['operationId' => 'delete-xor', 'source' => ''],
                        ],
                        '/however' => [
                            'get' => ['operationId' => 'get-however', 'source' => ''],
                            'put' => ['operationId' => 'put-however', 'source' => ''],
                            'post' => ['operationId' => 'post-however', 'source' => ''],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                        'paths' => [
                            '/and/{name}' => [
                                'get' => ['operationId' => 'get-and', 'source' => ''],
                                'put' => ['operationId' => 'put-and', 'source' => ''],
                                'post' => ['operationId' => 'post-and', 'source' => ''],
                            ],
                        ]
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
