<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Router\Collector;

use cebe\openapi\Reader;
use Membrane\OpenAPIRouter\Exception\CannotProcessOpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotRouteOpenAPI;
use Membrane\OpenAPIRouter\Router\Route\Route;
use Membrane\OpenAPIRouter\Router\RouteCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouteCollector::class)]
#[CoversClass(CannotRouteOpenAPI::class)]
#[UsesClass(Route::class)]
#[UsesClass(RouteCollection::class)]
class RouteCollectorTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/../../fixtures/';

    #[Test]
    public function throwExceptionIfThereAreNoRoutes(): void
    {
        $sut = new RouteCollector();
        $openApi = Reader::readFromJsonFile(self::FIXTURES . 'simple.json');

        self::expectExceptionObject(CannotRouteOpenAPI::noRoutes());

        $sut->collect($openApi);
    }

    #[Test]
    public function throwsExceptionForMissingOperationId(): void
    {
        $sut = new RouteCollector();
        $openApi = Reader::readFromYamlFile(self::FIXTURES . 'missingOperationId.yaml');

        self::expectExceptionObject(CannotProcessOpenAPI::missingOperationId('/path', 'get'));

        $sut->collect($openApi);
    }

    #[Test]
    public function throwsExceptionForDuplicateOperationId(): void
    {
        $sut = new RouteCollector();
        $openApi = Reader::readFromYamlFile(self::FIXTURES . 'duplicateOperationId.yaml');

        self::expectExceptionObject(CannotProcessOpenAPI::duplicateOperationId(
            'operation1',
            ['path' => '/path', 'operation' => 'get'],
            ['path' => '/path', 'operation' => 'delete'],
        ));

        $sut->collect($openApi);
    }

    public static function collectTestProvider(): array
    {
        return [
            'petstore-expanded.json' => [
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
            ],
            'WeirdAndWonderful.json' => [
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
            ],
        ];
    }

    #[Test]
    #[DataProvider('collectTestProvider')]
    public function collectTest(RouteCollection $expected, string $apiFilePath): void
    {
        $openApi = Reader::readFromJsonFile($apiFilePath);
        $sut = new RouteCollector();

        $actual = $sut->collect($openApi);

        self::assertEquals($expected, $actual);
    }
}
