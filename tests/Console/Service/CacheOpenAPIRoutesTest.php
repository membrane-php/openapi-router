<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Console\Service;

use Membrane\OpenAPIRouter\Console\Service\CacheOpenAPIRoutes;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\Route;
use Membrane\OpenAPIRouter\Router\RouteCollection;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(CacheOpenAPIRoutes::class)]
#[CoversClass(CannotCollectRoutes::class)]
#[UsesClass(RouteCollector::class)]
#[UsesClass(Route\Route::class), UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
#[UsesClass(RouteCollection::class)]
class CacheOpenAPIRoutesTest extends TestCase
{
    private string $root;
    private CacheOpenAPIRoutes $sut;
    private string $fixtures = __DIR__ . '/../../fixtures/';

    public function setUp(): void
    {
        $this->root = vfsStream::setup()->url();
        $this->sut = new CacheOpenAPIRoutes(self::createStub(LoggerInterface::class));
    }

    #[Test]
    public function outputsErrorForReadonlyFilePaths(): void
    {
        $cache = $this->root . '/cache';
        mkdir($cache);
        chmod($cache, 0444);

        self::assertFalse($this->sut->cache($this->fixtures . 'docs/petstore-expanded.json', $cache));
    }

    #[Test]
    public function cannotRouteWithoutPaths(): void
    {
        $openAPIFilePath = $this->root . '/openapi.json';
        file_put_contents(
            $openAPIFilePath,
            json_encode(['openapi' => '3.0.0', 'info' => ['title' => '', 'version' => '1.0.0'], 'paths' => []])
        );

        self::assertFalse($this->sut->cache($openAPIFilePath, $this->root . '/cache/routes.php'));
    }

    #[Test]
    public function cannotRouteFromRelativeFilePaths(): void
    {
        $filePath = './tests/fixtures/docs/petstore-expanded.json';

        self::assertTrue(file_exists($filePath));

        self::assertFalse($this->sut->cache($filePath, $this->root . '/cache/routes.php'));
    }

    public static function successfulExecutionProvider(): array
    {
        $petStoreRoutes = new RouteCollection([
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
        $weirdAndWonderfulRoutes = new RouteCollection([
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
        return [
            'successfully routes petstore-expanded.json' => [
                __DIR__ . '/../../fixtures/docs/petstore-expanded.json',
                vfsStream::url('root/cache/routes.php'),
                $petStoreRoutes
            ],
            'successfully routes the WeirdAndWonderful.json' => [
                __DIR__ . '/../../fixtures/WeirdAndWonderful.json',
                vfsStream::url('root/cache/routes.php'),
                $weirdAndWonderfulRoutes
            ],
            'successfully routes the WeirdAndWonderful.json and caches in a nested directory' => [
                __DIR__ . '/../../fixtures/WeirdAndWonderful.json',
                vfsStream::url('root/cache/nested-cache/nester-cache/nestest-cache/routes.php'),
                $weirdAndWonderfulRoutes
            ]
        ];
    }

    #[Test]
    #[DataProvider('successfulExecutionProvider')]
    public function successfulExecutionTest(
        string $openAPI,
        string $destination,
        RouteCollection $expectedRouteCollection
    ): void {
        self::assertTrue($this->sut->cache($openAPI, $destination));

        $actualRouteCollection = eval('?>' . file_get_contents($destination));

        self::assertEquals($expectedRouteCollection, $actualRouteCollection);
    }
}
