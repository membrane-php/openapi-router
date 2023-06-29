<?php

namespace Router;

use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\Router;
use Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(RouteCollector::class)]
#[CoversClass(OpenAPIFileReader::class)]
class APIeceOfCakeTest extends TestCase
{
    #[Test, TestDox('It reads and collects all s')]
    public function itCollectsPathsFromAPIeceOfCake(): RouteCollection
    {
        // Given the APIeceOfCake OpenAPI
        $apiFilePath = __DIR__ . '/../fixtures/APIeceOfCake.json';

        // When I read and collect the routes from APIeceOfCake
        $api = (new OpenAPIFileReader())->readFromAbsoluteFilePath($apiFilePath);
        $routeCollection = (new RouteCollector())->collect($api);

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
            '/{cakeType}/sponge' => ['get' => 'findSpongeByDesserts'],
            '/cakes/{icing}' => ['get' => 'findCakesByIcing', 'post' => 'addCakesByIcing'],
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
        $expectedOperationId = 'findSpongeCakes';
        $sut = new Router($routeCollection);

        $actualOperationId = $sut->route('/cakes/sponge', 'get');

        self::assertSame($expectedOperationId, $actualOperationId);
    }

    #[Test, TestDox('Paths with less dynamic elements should take priority')]
    #[Depends('itCollectsPathsFromAPIeceOfCake')]
    public function itRoutesToPartiallyDynamicBeforeCompletelyDynamic(RouteCollection $routeCollection): void
    {
        $expectedOperationId = 'findCakesByIcing';
        $sut = new Router($routeCollection);

        $actualOperationId = $sut->route('/cakes/chocolate', 'get');

        self::assertSame($expectedOperationId, $actualOperationId);
    }
}
