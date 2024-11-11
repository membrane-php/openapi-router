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
}
