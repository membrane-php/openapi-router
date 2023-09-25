<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests;

use Generator;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouteCollection::class)]
#[UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
class RouteCollectionTest extends TestCase
{
    public static function provideServers(): Generator
    {
        $expected = new RouteCollection([
                'hosted' => [
                    'static' => [
                        'https://www.server.io' => [
                            'static' => [
                                '/static/path' => ['post' => 'post-static-path'],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/([^/]+)/path(*MARK:/{dynamic}/path)|/([^/]+)/([^/]+)/path(*MARK:/{very}/{dynamic}/path))$#',
                                'paths' => [
                                    '/{dynamic}/path' => ['get' => 'get-dynamic-path'],
                                    '/{very}/{dynamic}/path' => ['patch' => 'patch-very-dynamic-path']
                                ]
                            ]
                        ]

                    ],
                    'dynamic' => [
                        'regex' => '#^(?|https://www.server.net/([^/]+)(*MARK:https://www.server.net/{version})|https://([^/]+).server.net/([^/]+)(*MARK:https://{environment}.server.net/{version}))#',
                        'servers' => [
                            'https://www.server.net/{version}' => [
                                'static' => [
                                    '/static/path' => [
                                        'delete' => 'delete-static-path',
                                        'get' => 'get-static-path',
                                    ]
                                ],
                                'dynamic' => [
                                    'regex' => '#^(?|/([^/]+)/path(*MARK:/{dynamic}/path))$#',
                                    'paths' => [
                                        '/{dynamic}/path' => ['post' => 'post-dynamic-path']
                                    ],
                                ]
                            ],
                            'https://{environment}.server.net/{version}' => [
                                'static' => [],
                                'dynamic' => [
                                    'regex' => '#^(?|/([^/]+)/([^/]+)/([^/]+)(*MARK:/{very}/{dynamic}/{path}}))$#',
                                    'paths' => [
                                        '/{very}/{dynamic}/{path}}' => ['post' => 'post-very-dynamic-path']
                                    ],
                                ]
                            ],
                        ]
                    ]
                ],
                'hostless' => [
                    'static' => [
                        '' => [
                            'static' => [],
                            'dynamic' => [
                                'regex' => '#^(?|/([^/]+)/([^/]+)/([^/]+)(*MARK:/{very}/{dynamic}/{path}}))$#',
                                'paths' => [
                                    '/{very}/{dynamic}/{path}}' => ['get' => 'get-very-dynamic-path']
                                ]
                            ]
                        ],
                    ],
                    'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]
                ],
        ]);

        $servers = [
            (function() {
                $server = new Route\Server('https://www.server.net/{version}');
                $server->addRoute('/static/path', 'get', 'get-static-path');
                $server->addRoute('/{dynamic}/path', 'post', 'post-dynamic-path');
                $server->addRoute('/static/path', 'delete', 'delete-static-path');
                return $server;
            })(),
            (function() {
                $server = new Route\Server('https://www.server.io');
                $server->addRoute('/{very}/{dynamic}/path', 'patch', 'patch-very-dynamic-path');
                $server->addRoute('/{dynamic}/path', 'get', 'get-dynamic-path');
                $server->addRoute('/static/path', 'post', 'post-static-path');
                return $server;
            })(),
            (function() {
                $server = new Route\Server('https://{environment}.server.net/{version}');
                $server->addRoute('/{very}/{dynamic}/{path}}', 'post', 'post-very-dynamic-path');
                return $server;
            })(),
            (function() {
                $server = new Route\Server('');
                $server->addRoute('/{very}/{dynamic}/{path}}', 'get', 'get-very-dynamic-path');
                return $server;
            })(),
        ];

        yield [$expected, ...$servers];
        yield [$expected, ...array_reverse($servers)];
    }

    #[Test]
    #[DataProvider('provideServers')]
    public function itCanBeConstructedFromServers(RouteCollection $expected, Route\Server ...$servers): void
    {
        self::assertEquals($expected, RouteCollection::fromServers(...$servers));
    }
}
