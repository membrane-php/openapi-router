<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Router\Collector;

use cebe\openapi\Reader;
use Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Membrane\OpenAPIRouter\Router\Collector\RouteCollector
 * @uses   \Membrane\OpenAPIRouter\Router\ValueObject\Route
 * @uses \Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection
 */
class RouteCollectorTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/../../fixtures/';

    /** @test */
    public function throwExceptionIfThereAreNoRoutes(): void
    {
        $sut = new RouteCollector();
        $openApi = Reader::readFromJsonFile(self::FIXTURES . 'simple.json');

        self::expectExceptionObject(new \Exception());

        $sut->collect($openApi);
    }

    public function collectTestProvider(): array
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
                                    'regex' => '#^(?|/pets/([^/])(*MARK:/pets/{id}))$#',
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
                                    'regex' => '#^(?|/and/([^/])(*MARK:/and/{name}))$#',
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
                                    'regex' => '#^(?|/and/([^/])(*MARK:/and/{name}))$#',
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
                        ],
                        'dynamic' => [
                            'regex' => '#^(?|http://weird.io/([^/])(*MARK:http://weird.io/{conjunction}))#',
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
                            'regex' => '#^(?|)#',
                            'servers' => [],
                        ],
                    ],
                ]),
                self::FIXTURES . 'WeirdAndWonderful.json',
            ],
        ];
    }

    /**
     * Tests it can collect methods => operationIds with an index matching the regex capturing group
     *
     * @test
     * @dataProvider collectTestProvider
     */
    public function collectTest(RouteCollection $expected, string $apiFilePath): void
    {
        $openApi = Reader::readFromJsonFile($apiFilePath);
        $sut = new RouteCollector();

        $actual = $sut->collect($openApi);

        self::assertEquals($expected, $actual);
    }
}
