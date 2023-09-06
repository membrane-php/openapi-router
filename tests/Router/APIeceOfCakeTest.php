<?php

namespace Membrane\OpenAPIRouter\Tests\Router;

use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIReader\Reader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\Route;
use Membrane\OpenAPIRouter\Router\RouteCollection;
use Membrane\OpenAPIRouter\Router\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(RouteCollector::class)]
#[UsesClass(RouteCollection::class)]
#[UsesClass(Route\Route::class), UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
class APIeceOfCakeTest extends TestCase
{
    #[Test, TestDox('It reads and collects all s')]
    public function itCollectsPathsFromAPIeceOfCake(): RouteCollection
    {
        // Given the APIeceOfCake OpenAPI
        $apiFilePath = __DIR__ . '/../fixtures/APIeceOfCake.json';

        // When I read and collect the routes from APIeceOfCake
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0, OpenAPIVersion::Version_3_1]))
            ->readFromAbsoluteFilePath($apiFilePath);
        $routeCollection = (new RouteCollector())->collect($openAPI);

        // I expect to have no hosted routes
        self::assertEmpty($routeCollection->routes['hosted']['static']);
        self::assertEmpty($routeCollection->routes['hosted']['dynamic']['servers']);

        // I expect to have one hostless static route
        self::assertSame(
            'findSpongeCakes',
            $routeCollection->routes['hostless']['static']['']['static']['/cakes/sponge']['get']
        );

        // I expect to have the following hostless dynamic routes
        $hostlessDynamicRoutes = [
            '/cakes/{icing}' => ['get' => 'findCakesByIcing', 'post' => 'addCakesByIcing'],
            '/{cakeType}/sponge' => ['get' => 'findSpongeByDesserts'],
            '/{cakeType}/{icing}' => ['get' => 'findDessertByIcing', 'post' => 'addDessertByIcing'],
        ];

        self::assertSame(
            $hostlessDynamicRoutes,
            $routeCollection->routes['hostless']['static']['']['dynamic']['paths']
        );

        return $routeCollection;
    }

    #[Test, TestDox('Completely static paths should take priority over any other')]
    #[Depends('itCollectsPathsFromAPIeceOfCake')]
    public function itRoutesToCompletelyStaticPathFirst(RouteCollection $routeCollection): void
    {
        $sut = new Router($routeCollection);

        $actualOperationId = $sut->route('/cakes/sponge', 'get');

        self::assertSame('findSpongeCakes', $actualOperationId);
    }

    #[Test, TestDox('Paths with less dynamic elements should take priority')]
    #[Depends('itCollectsPathsFromAPIeceOfCake')]
    public function itRoutesToPartiallyDynamicBeforeCompletelyDynamic(RouteCollection $routeCollection): void
    {
        $sut = new Router($routeCollection);

        $actualOperationId = $sut->route('/cakes/chocolate', 'get');

        self::assertSame('findCakesByIcing', $actualOperationId);
    }
}
