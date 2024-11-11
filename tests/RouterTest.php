<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests;

use Generator;
use Membrane\OpenAPIReader\FileFormat;
use Membrane\OpenAPIReader\MembraneReader;
use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIRouter\Exception;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use Membrane\OpenAPIRouter\Router;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesPetstoreExpanded;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesWeirdAndWonderful;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(Exception\CannotRouteRequest::class)]
#[UsesClass(RouteCollection::class)]
#[UsesClass(RouteCollector::class)]
#[UsesClass(Route\Path::class), UsesClass(Route\Server::class)]
class RouterTest extends TestCase
{
    public static function unsuccessfulRouteProvider(): array
    {
        return [
            'petstore-expanded: incorrect server url' => [
                Exception\CannotRouteRequest::notFound(),
                'https://hatshop.dapper.net/api/pets',
                'get',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
            'petstore-expanded: correct static server url but incorrect path' => [
                Exception\CannotRouteRequest::notFound(),
                'http://petstore.swagger.io/api/hats',
                'get',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
            'WeirdAndWonderful: correct dynamic erver url but incorrect path' => [
                Exception\CannotRouteRequest::notFound(),
                'http://weird.io/however/but',
                'get',
                ProvidesWeirdAndWonderful::getRoutes(),
            ],
            'petstore-expanded: correct url but incorrect method' => [
                Exception\CannotRouteRequest::methodNotAllowed(),
                'http://petstore.swagger.io/api/pets',
                'delete',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
        ];
    }

    #[Test]
    #[DataProvider('unsuccessfulRouteProvider')]
    public function unsuccessfulRouteTest(
        Exception\CannotRouteRequest $expected,
        string $path,
        string $method,
        RouteCollection $operationCollection
    ): void {
        $sut = new Router($operationCollection);

        self::expectExceptionObject($expected);

        $sut->route($path, $method);
    }

    public static function successfulRouteProvider(): array
    {
        return [
            'petstore: /pets path, get method' => [
                'findPets',
                'http://petstore.swagger.io/api/pets',
                'get',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
            'petstore: /pets/{id} path, get method' => [
                'find pet by id',
                'http://petstore.swagger.io/api/pets/1',
                'get',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
            'petstore: /pets/{id} path, delete method' => [
                'deletePet',
                'http://petstore.swagger.io/api/pets/1',
                'delete',
                ProvidesPetstoreExpanded::getRoutes(),
            ],
            'WeirdAndWonderful: /v1/or path, post method' => [
                'post-or',
                '/v1/or',
                'post',
                ProvidesWeirdAndWonderful::getRoutes(),
            ],
            'WeirdAndWonderful: http://www.arbitrary.com/v1/or path, post method' => [
                'post-or',
                '/v1/or',
                'post',
                ProvidesWeirdAndWonderful::getRoutes(),
            ],
            'WeirdAndWonderful: http://weird.io/however/or path, post method' => [
                'post-or',
                'http://weird.io/however/or',
                'post',
                ProvidesWeirdAndWonderful::getRoutes(),
            ],
            'WeirdAndWonderful: /{version}/xor path, delete method' => [
                'delete-xor',
                '/12/xor',
                'delete',
                ProvidesWeirdAndWonderful::getRoutes(),
            ],
        ];
    }

    public static function providePathsToPrioritise(): Generator
    {
        $operation = fn(string $operationId) => [
            'operationId' => $operationId,
            'responses' => [200 => ['description' => '']]
        ];
        $pathParameter = fn(string $name) => [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string']
        ];

        $testCases = [
            'static > dynamic' => [
                '/static/path',
                [
                    '/static/path' => ['get' => $operation('first')],
                    '/{dynamic}/{path}' => [
                        'get' => $operation('second'),
                        'parameters' => [$pathParameter('dynamic'), $pathParameter('path')]
                    ]
                ]
            ],
            'partially dynamic > completely dynamic' => [
                '/which/path',
                [
                    '/{partially-dynamic}/path' => [
                        'get' => $operation('first'),
                        'parameters' => [$pathParameter('partially-dynamic')]
                    ],
                    '/{dynamic}/{path}' => [
                        'get' => $operation('second'),
                        'parameters' => [$pathParameter('dynamic'), $pathParameter('path')]
                    ],
                ]
            ],
            'less dynamic components > more dynamic components' => [
                '/which/path/to/pick',
                [
                    '/{dynamic}/path/to/pick' => [
                        'get' => $operation('first'),
                        'parameters' => [$pathParameter('partially-dynamic')]
                    ],
                    '/{dynamic}/{path}/to/pick' => [
                        'get' => $operation('second'),
                        'parameters' => [$pathParameter('dynamic'), $pathParameter('path')]
                    ],
                ]
            ]
        ];

        $openAPI = fn(array $paths) => json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => '', 'version' => '1.0.0'],
            'paths' => $paths,
        ]);

        foreach ($testCases as $description => $testCase) {
            yield $description => [$testCase[0], $openAPI($testCase[1])];
            yield "$description (order of paths reversed)" => [$testCase[0], $openAPI(array_reverse($testCase[1]))];
        }
    }

    #[Test, TestDox('Relative urls are prioritised based on the number of dynamic components they have, least first')]
    #[DataProvider('providePathsToPrioritise')]
    public function itPrioritisesPathsCorrectly(string $url, string $openAPI): void
    {
        $openAPI = (new MembraneReader([OpenAPIVersion::Version_3_0]))
            ->readFromString($openAPI, FileFormat::Json);

        $routeCollection = (new RouteCollector())
            ->collect($openAPI);

        $priority = (new Router($routeCollection))
            ->route($url, 'get');

        self::assertSame('first', $priority);
    }

    public static function provideServersToPrioritise(): Generator
    {
        $minimalPath = fn(string $operationId) => [
            'get' => [
                'operationId' => $operationId,
                'responses' => [200 => ['description' => '']]
            ]
        ];

        $testCases = [
            'longer > shorter' => [
                'http://this/path/please',
                [['url' => 'http://this'], ['url' => 'http://this/path']],
                ['/please' => $minimalPath('first'), '/path/please' => $minimalPath('second')]
            ],
            'static > dynamic' => [
                'http://this/path/please',
                [
                    ['url' => 'http://this'],
                    [
                        'url' => 'http://{demonstrative-pronoun}/path',
                        'variables' => ['demonstrative-pronoun' => ['default' => 'this']]
                    ]
                ],
                ['/path/please' => $minimalPath('first'), '/please' => $minimalPath('second')]
            ],
            'less dynamic components > more dynamic components' => [
                'http://this/path/pretty/please',
                [
                    [
                        'url' => 'http://{demonstrative-pronoun}/{route}/pretty',
                        'variables' => [
                            'demonstrative-pronoun' => ['default' => 'this'],
                            'route' => ['default' => 'path']
                        ]
                    ],
                    [
                        'url' => 'http://{demonstrative-pronoun}/path',
                        'variables' => ['demonstrative-pronoun' => ['default' => 'this']]
                    ],
                ],
                ['/pretty/please' => $minimalPath('first'), '/please' => $minimalPath('second')]
            ]

        ];

        $openAPI = fn(array $servers, array $paths) => json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => '', 'version' => '1.0.0'],
            'servers' => $servers,
            'paths' => $paths,
        ]);

        foreach ($testCases as $description => $testCase) {
            yield $description => [
                $testCase[0],
                $openAPI($testCase[1], $testCase[2])
            ];
            yield "$description (order of servers reversed)" => [
                $testCase[0],
                $openAPI(array_reverse($testCase[1]), $testCase[2])
            ];
            yield "$description (order of paths reversed)" => [
                $testCase[0],
                $openAPI($testCase[1], array_reverse($testCase[2]))
            ];
            yield "$description (order of paths and servers reversed)" => [
                $testCase[0],
                $openAPI(array_reverse($testCase[1]), array_reverse($testCase[2]))
            ];
        }
    }

    #[Test, TestDox('Servers are prioritised by length and number of dynamic components')]
    #[DataProvider('provideServersToPrioritise')]
    public function itPrioritisesServersCorrectly(string $url, string $openAPI): void
    {
        $openAPI = (new MembraneReader([OpenAPIVersion::Version_3_0]))
            ->readFromString($openAPI, FileFormat::Json);

        $routeCollection = (new RouteCollector())
            ->collect($openAPI);

        $priority = (new Router($routeCollection))
            ->route($url, 'get');

        self::assertSame('first', $priority);
    }
}
