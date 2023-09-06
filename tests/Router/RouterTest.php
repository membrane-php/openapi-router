<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Router;

use Membrane\OpenAPIRouter\Exception\CannotRouteRequest;
use Membrane\OpenAPIRouter\Router\RouteCollection;
use Membrane\OpenAPIRouter\Router\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(CannotRouteRequest::class)]
class RouterTest extends TestCase
{
    private static function getPetStoreRouteCollection(): RouteCollection
    {
        return new RouteCollection([
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
        ]);
    }

    private static function getWeirdAndWonderfulRouteCollection(): RouteCollection
    {
        return new RouteCollection([
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
        ]);
    }

    public static function unsuccessfulRouteProvider(): array
    {
        return [
            'petstore-expanded: incorrect server url' => [
                CannotRouteRequest::notFound(),
                'https://hatshop.dapper.net/api/pets',
                'get',
                self::getPetStoreRouteCollection(),
            ],
            'petstore-expanded: correct static server url but incorrect path' => [
                CannotRouteRequest::notFound(),
                'http://petstore.swagger.io/api/hats',
                'get',
                self::getPetStoreRouteCollection(),
            ],
            'WeirdAndWonderful: correct dynamic erver url but incorrect path' => [
                CannotRouteRequest::notFound(),
                'http://weird.io/however/but',
                'get',
                self::getWeirdAndWonderfulRouteCollection(),
            ],
            'petstore-expanded: correct url but incorrect method' => [
                CannotRouteRequest::methodNotAllowed(),
                'http://petstore.swagger.io/api/pets',
                'delete',
                self::getPetStoreRouteCollection(),
            ],
        ];
    }

    #[Test]
    #[DataProvider('unsuccessfulRouteProvider')]
    public function unsuccessfulRouteTest(
        CannotRouteRequest $expected,
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
                self::getPetStoreRouteCollection(),
            ],
            'petstore: /pets/{id} path, get method' => [
                'find pet by id',
                'http://petstore.swagger.io/api/pets/1',
                'get',
                self::getPetStoreRouteCollection(),
            ],
            'petstore: /pets/{id} path, delete method' => [
                'deletePet',
                'http://petstore.swagger.io/api/pets/1',
                'delete',
                self::getPetStoreRouteCollection(),
            ],
            'WeirdAndWonderful: /v1/or path, post method' => [
                'post-or',
                '/v1/or',
                'post',
                self::getWeirdAndWonderfulRouteCollection(),
            ],
            'WeirdAndWonderful: http://www.arbitrary.com/v1/or path, post method' => [
                'post-or',
                '/v1/or',
                'post',
                self::getWeirdAndWonderfulRouteCollection(),
            ],
            'WeirdAndWonderful: http://weird.io/however/or path, post method' => [
                'post-or',
                'http://weird.io/however/or',
                'post',
                self::getWeirdAndWonderfulRouteCollection(),
            ],
            'WeirdAndWonderful: /{version}/xor path, delete method' => [
                'delete-xor',
                '/12/xor',
                'delete',
                self::getWeirdAndWonderfulRouteCollection(),
            ],
        ];
    }

    #[Test]
    #[DataProvider('successfulRouteProvider')]
    public function successfulRouteTest(
        string $expected,
        string $path,
        string $method,
        RouteCollection $operationCollection
    ): void {
        $sut = new Router($operationCollection);

        $actual = $sut->route($path, $method);

        self::assertSame($expected, $actual);
    }
}
