<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests;

use Generator;
use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIReader\Reader;
use Membrane\OpenAPIRouter\Exception\CannotRouteRequest;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use Membrane\OpenAPIRouter\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(CannotRouteRequest::class)]
class RouterTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/';

    private static function getPetStoreRouteCollection(): RouteCollection
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0]))
                ->readFromAbsoluteFilePath(self::FIXTURES . 'docs/petstore-expanded.json');

        return (new RouteCollector())->collect($openAPI);
    }

    private static function getWeirdAndWonderfulRouteCollection(): RouteCollection
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0]))
            ->readFromAbsoluteFilePath(self::FIXTURES . 'WeirdAndWonderful.json');

        return (new RouteCollector())->collect($openAPI);
    }

    private static function getAPieceOfCakeRouteCollection(): RouteCollection
    {
        $openAPI = (new Reader([OpenAPIVersion::Version_3_0]))
            ->readFromAbsoluteFilePath(self::FIXTURES . 'APIeceOfCake.json');

        return (new RouteCollector())->collect($openAPI);
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

    public static function provideRoutingPriorities(): Generator
    {
        yield 'completely static path prioritise over anything dynamic' => [
            'findSpongeCakes',
            '/cakes/sponge',
            'get',
            self::getAPieceOfCakeRouteCollection()
        ];
        yield 'partially dynamic path to prioritise over anything with more dynamic parts' => [
            'findCakesByIcing',
            '/cakes/chocolate',
            'get',
            self::getAPieceOfCakeRouteCollection()
        ];
    }

    #[Test, TestDox('When routing the priority will be paths with less dynamic components first')]
    #[DataProvider('provideRoutingPriorities')]
    public function itWillPrioritiseRoutesWithMoreStaticComponentsFirst(
        string $expectedOperationId,
        string $url,
        string $method,
        RouteCollection $routeCollection
    ): void {
        $sut = new Router($routeCollection);

        $actualOperationId = $sut->route($url, $method);

        self::assertSame($expectedOperationId, $actualOperationId);
    }
}
