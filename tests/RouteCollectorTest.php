<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests;

use Generator;
use Membrane\OpenAPIReader\FileFormat;
use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIReader\Reader;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouteCollector::class)]
#[CoversClass(CannotCollectRoutes::class)]
#[UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
#[UsesClass(RouteCollection::class)]
class RouteCollectorTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/fixtures/';

    #[Test]
    public function throwExceptionIfThereAreNoRoutes(): void
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0, OpenAPIVersion::Version_3_1]))
            ->readFromString(
                json_encode([
                    'openapi' => '3.0.0',
                    'info' => ['title' => '', 'version' => '1.0.0'],
                    'paths' => []
                ]),
                FileFormat::Json
            );

        self::expectExceptionObject(CannotCollectRoutes::noRoutes());

        (new RouteCollector())->collect($openAPI);
    }

    #[Test]
    public function removesDuplicateServers(): void
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0, OpenAPIVersion::Version_3_1]))
            ->readFromString(
                json_encode([
                    'openapi' => '3.0.0',
                    'info' => ['title' => '', 'version' => '1.0.0'],
                    'servers' => [
                        ['url' => 'https://www.server.net'],
                        ['url' => 'https://www.server.net/'],
                    ],
                    'paths' => [
                        '/path' => [
                            'get' => [
                                'operationId' => 'get-path',
                                'responses' => [200 => ['description' => 'Successful Response']]
                            ]
                        ]
                    ]
                ]),
                FileFormat::Json
            );

        $routeCollection = (new RouteCollector())->collect($openAPI);

        self::assertCount(1, $routeCollection->routes['hosted']['static']);
    }

    public static function collectTestProvider(): Generator
    {
        yield 'petstore-expanded.json' => [
            new RouteCollection([
                'hosted' => [
                    'static' => [
                        'http://petstore.swagger.io/api' => [
                            'static' => [
                                '/pets' => [
                                    'get' => 'findPets',
                                    'post' => 'addPet',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                                'paths' => [
                                    '/pets/{id}' => [
                                        'get' => 'find pet by id',
                                        'delete' => 'deletePet',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|)#',
                        'servers' => [],
                    ],
                ],
                'hostless' => [
                    'static' => [],
                    'dynamic' => [
                        'regex' => '#^(?|)#',
                        'servers' => [],
                    ],
                ],
            ]),
            self::FIXTURES . 'docs/petstore-expanded.json',
        ];

        yield 'WeirdAndWonderful.json' => [
            new RouteCollection([
                'hosted' => [
                    'static' => [
                        'http://weirdest.com' => [
                            'static' => [
                                '/however' => [
                                    'put' => 'put-however',
                                    'post' => 'post-however',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                                'paths' => [
                                    '/and/{name}' => [
                                        'get' => 'get-and',
                                    ],
                                ],
                            ],
                        ],
                        'http://weirder.co.uk' => [
                            'static' => [
                                '/however' => [
                                    'get' => 'get-however',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/and/([^/]+)(*MARK:/and/{name}))$#',
                                'paths' => [
                                    '/and/{name}' => [
                                        'put' => 'put-and',
                                        'post' => 'post-and',
                                    ],
                                ],
                            ],
                        ],
                        'http://wonderful.io' => [
                            'static' => [
                                '/or' => [
                                    'post' => 'post-or',
                                ],
                                '/xor' => [
                                    'delete' => 'delete-xor',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|)$#',
                                'paths' => [],
                            ],
                        ],
                        'http://wonderful.io/and' => [
                            'static' => [
                                '/or' => [
                                    'post' => 'post-or',
                                ],
                                '/xor' => [
                                    'delete' => 'delete-xor',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|)$#',
                                'paths' => [],
                            ],
                        ],
                        'http://wonderful.io/or' => [
                            'static' => [
                                '/or' => [
                                    'post' => 'post-or',
                                ],
                                '/xor' => [
                                    'delete' => 'delete-xor',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|)$#',
                                'paths' => [],
                            ],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|http://weird.io/([^/]+)(*MARK:http://weird.io/{conjunction}))#',
                        'servers' => [
                            'http://weird.io/{conjunction}' => [
                                'static' => [
                                    '/or' => [
                                        'post' => 'post-or',
                                    ],
                                    '/xor' => [
                                        'delete' => 'delete-xor',
                                    ],
                                ],
                                'dynamic' => [
                                    'regex' => '#^(?|)$#',
                                    'paths' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                'hostless' => [
                    'static' => [
                        '' => [
                            'static' => [
                                '/or' => [
                                    'post' => 'post-or',
                                ],
                                '/xor' => [
                                    'delete' => 'delete-xor',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|)$#',
                                'paths' => [],
                            ],
                        ],
                        '/v1' => [
                            'static' => [
                                '/or' => [
                                    'post' => 'post-or',
                                ],
                                '/xor' => [
                                    'delete' => 'delete-xor',
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|)$#',
                                'paths' => [],
                            ],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/([^/]+)(*MARK:/{version}))#',
                        'servers' => [
                            '/{version}' => [
                                'static' => [
                                    '/or' => [
                                        'post' => 'post-or',
                                    ],
                                    '/xor' => [
                                        'delete' => 'delete-xor',
                                    ],
                                ],
                                'dynamic' => [
                                    'regex' => '#^(?|)$#',
                                    'paths' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
            self::FIXTURES . 'WeirdAndWonderful.json',
        ];
        yield 'APieceOfCake.json' => [
            new RouteCollection([
                'hosted' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
                'hostless' => [
                    'static' => [
                        '' => [
                            'static' => ['/cakes/sponge' => ['get' => 'findSpongeCakes']],
                            'dynamic' => [
                                'regex' => '#^(?|' .
                                    '/cakes/([^/]+)(*MARK:/cakes/{icing})|' .
                                    '/([^/]+)/sponge(*MARK:/{cakeType}/sponge)|' .
                                    '/([^/]+)/([^/]+)(*MARK:/{cakeType}/{icing})' .
                                    ')$#',
                                'paths' => [
                                    '/cakes/{icing}' => ['get' => 'findCakesByIcing', 'post' => 'addCakesByIcing'],
                                    '/{cakeType}/sponge' => ['get' => 'findSpongeByDesserts'],
                                    '/{cakeType}/{icing}' => [
                                        'get' => 'findDessertByIcing',
                                        'post' => 'addDessertByIcing'
                                    ]
                                ]
                            ]
                        ],
                    ],
                    'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]
                ],
            ]),
            self::FIXTURES . 'APIeceOfCake.json'
        ];
    }

    #[Test]
    #[DataProvider('collectTestProvider')]
    public function collectTest(RouteCollection $expected, string $apiFilePath): void
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0, OpenAPIVersion::Version_3_1]))
            ->readFromAbsoluteFilePath($apiFilePath);

        self::assertEquals($expected, (new RouteCollector())->collect($openAPI));
    }
}
