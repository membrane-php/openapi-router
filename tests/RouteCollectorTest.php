<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests;

use Generator;
use Membrane\OpenAPIReader\FileFormat;
use Membrane\OpenAPIReader\MembraneReader;
use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesApiAndRoutes;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesPetstoreExpanded;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesTrainTravel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouteCollector::class)]
#[CoversClass(CannotCollectRoutes::class)]
#[UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
#[UsesClass(RouteCollection::class)]
class RouteCollectorTest extends TestCase
{
    #[Test]
    public function itThrowsIfNoRoutes(): void
    {
        $openAPI = (new MembraneReader([OpenAPIVersion::Version_3_0]))
            ->readFromString(json_encode([
                'openapi' => '3.0.0',
                'info' => ['title' => '', 'version' => '1.0.0'],
                'paths' => []
            ]), FileFormat::Json);

        self::expectExceptionObject(CannotCollectRoutes::noRoutes());

        (new RouteCollector())->collect($openAPI);
    }

    #[Test]
    public function itRemovesDuplicateServers(): void
    {
        $openAPI = (new MembraneReader([OpenAPIVersion::Version_3_0]))
            ->readFromString(json_encode([
                'openapi' => '3.0.0',
                'info' => ['title' => '', 'version' => '1.0.0'],
                'servers' => [
                    ['url' => 'https://www.server.net'],
                    ['url' => 'https://www.server.net/'],
                ],
                'paths' => ['/path' => ['get' => [
                    'operationId' => 'get-path',
                    'responses' => [200 => ['description' => 'Successful Response']]
                ]]]
            ]), FileFormat::Json);

        $routeCollection = (new RouteCollector())->collect($openAPI);

        self::assertCount(1, $routeCollection->routes['hosted']['static']);
    }

    #[Test]
    #[DataProviderExternal(ProvidesApiAndRoutes::class, 'defaultBehaviour')]
    public function collectTest(string $apiFilePath, RouteCollection $expected): void
    {
        $openAPI = (new MembraneReader([
            OpenAPIVersion::Version_3_0,
            OpenAPIVersion::Version_3_1,
        ]))->readFromAbsoluteFilePath($apiFilePath);

        self::assertEquals($expected, (new RouteCollector())->collect($openAPI));
    }

    #[Test]
    public function collectManyTest(): void
    {
        $expected = new RouteCollection(
            [
                'hosted' => [
                    'static' => [
                        'http://petstore.swagger.io/api' => [
                            'static' => [
                                '/pets' => [
                                    'get' => [
                                        'operationId' => 'findPets',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/docs/petstore-expanded.json',
                                    ],
                                    'post' => [
                                        'operationId' => 'addPet',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/docs/petstore-expanded.json',
                                    ],
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/pets/([^/]+)(*MARK:/pets/{id}))$#',
                                'paths' => [
                                    '/pets/{id}' => [
                                        'delete' => [
                                            'operationId' => 'deletePet',
                                            'source' => '/home/imhotek/git/openapi-router/tests/fixtures/docs/petstore-expanded.json',
                                        ],
                                        'get' => [
                                            'operationId' => 'find pet by id',
                                            'source' => '/home/imhotek/git/openapi-router/tests/fixtures/docs/petstore-expanded.json',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'https://api.example.com' => [
                            'static' => [
                                '/stations' => [
                                    'get' => [
                                        'operationId' => 'get-stations',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                    ],
                                ],
                                '/trips' => [
                                    'get' => [
                                        'operationId' => 'get-trips',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                    ],
                                ],
                                '/bookings' => [
                                    'get' => [
                                        'operationId' => 'get-bookings',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                    ],
                                    'post' => [
                                        'operationId' => 'create-booking',
                                        'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                    ],
                                ],
                            ],
                            'dynamic' => [
                                'regex' => '#^(?|/bookings/([^/]+)(*MARK:/bookings/{bookingId})|/bookings/([^/]+)/payment(*MARK:/bookings/{bookingId}/payment))$#',
                                'paths' => [
                                    '/bookings/{bookingId}' => [
                                        'delete' => [
                                            'operationId' => 'delete-booking',
                                            'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                        ],
                                        'get' => [
                                            'operationId' => 'get-booking',
                                            'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                        ],
                                    ],
                                    '/bookings/{bookingId}/payment' => [
                                        'post' => [
                                            'operationId' => 'create-booking-payment',
                                            'source' => '/home/imhotek/git/openapi-router/tests/fixtures/train-travel-api.yaml',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|)#',
                        'servers' => [
                        ],
                    ],
                ],
                'hostless' => [
                    'static' => [
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|)#',
                        'servers' => [
                        ],
                    ],
                ],
            ]
        );

        $specs = $collection = [];

        foreach ([ProvidesPetstoreExpanded::class, ProvidesTrainTravel::class] as $class) {
            $filePath = $class::getFilePath();
            $specs[$filePath] = new MembraneReader([
                OpenAPIVersion::Version_3_0,
                OpenAPIVersion::Version_3_1,
            ])->readFromAbsoluteFilePath($filePath);
        }


        self::assertEquals($expected, (new RouteCollector())->collectMany($specs));
    }
}
