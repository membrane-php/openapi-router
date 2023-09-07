<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Console\Command;

use Membrane\OpenAPIRouter\Console\Command\CacheOpenAPIRoutes;
use Membrane\OpenAPIRouter\Console\Service;
use Membrane\OpenAPIRouter\Exception;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CacheOpenAPIRoutes::class)]
#[CoversClass(Exception\CannotCollectRoutes::class)]
#[UsesClass(Service\CacheOpenAPIRoutes::class)]
#[UsesClass(Route\Route::class), UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
#[UsesClass(RouteCollection::class)]
#[UsesClass(RouteCollector::class)]
class CacheOpenAPIRoutesTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('cache')->url();
    }

    #[Test]
    public function outputsErrorForReadonlyFilePaths(): void
    {
        $correctApiPath = __DIR__ . '/../../fixtures/docs/petstore-expanded.json';
        chmod(vfsStream::url('cache'), 0444);
        $readonlyDestination = vfsStream::url('cache');
        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute(['openAPI' => $correctApiPath, 'destination' => $readonlyDestination]);

        self::assertSame(Command::FAILURE, $sut->getStatusCode());

        self::assertSame(
            sprintf('[error] %s cannot be written to', vfsStream::url('cache')),
            trim($sut->getDisplay(true))
        );
    }

    #[Test]
    public function itCannotRouteFromRelativeFilePaths(): void
    {
        $filePath = './tests/fixtures/docs/petstore-expanded.json';

        self::assertTrue(file_exists($filePath));

        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute(['openAPI' => $filePath, 'destination' => vfsStream::url('cache') . '/routes.php']);

        self::assertSame(Command::FAILURE, $sut->getStatusCode());
    }

    #[Test]
    public function itCannotRouteWithoutAnyRoutes(): void
    {
        $openAPIFilePath = $this->root . '/openapi.json';
        file_put_contents(
            $openAPIFilePath,
            json_encode([
                'openapi' => '3.0.0',
                'info' => ['title' => '', 'version' => '1.0.0'],
                'paths' => []
            ])
        );

        self::assertTrue(file_exists($openAPIFilePath));

        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute(['openAPI' => $openAPIFilePath, 'destination' => vfsStream::url('cache') . '/routes.php']);

        self::assertSame(Command::FAILURE, $sut->getStatusCode());
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
                vfsStream::url('cache/routes.php'),
                Command::SUCCESS,
                $petStoreRoutes
            ],
            'successfully routes the WeirdAndWonderful.json' => [
                __DIR__ . '/../../fixtures/WeirdAndWonderful.json',
                vfsStream::url('cache/routes.php'),
                Command::SUCCESS,
                $weirdAndWonderfulRoutes
            ],
            'successfully routes the WeirdAndWonderful.json and caches in a nested directory' => [
                __DIR__ . '/../../fixtures/WeirdAndWonderful.json',
                vfsStream::url('cache/nested-cache/nester-cache/nestest-cache/routes.php'),
                Command::SUCCESS,
                $weirdAndWonderfulRoutes
            ]
        ];
    }

    #[Test]
    #[DataProvider('successfulExecutionProvider')]
    public function successfulExecutionTest(
        string $openAPI,
        string $destination,
        int $expectedStatusCode,
        RouteCollection $expectedRouteCollection
    ): void {
        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute(['openAPI' => $openAPI, 'destination' => $destination]);

        self::assertSame($expectedStatusCode, $sut->getStatusCode());

        $actualRouteCollection = eval('?>' . file_get_contents($destination));

        self::assertEquals($expectedRouteCollection, $actualRouteCollection);
    }
}
