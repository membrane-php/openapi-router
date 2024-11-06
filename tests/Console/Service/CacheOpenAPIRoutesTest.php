<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Console\Service;

use Membrane\OpenAPIRouter\Console\Service\CacheOpenAPIRoutes;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\Route;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use Membrane\OpenAPIRouter\Tests\Fixtures\ProvidesApiAndRoutes;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(CacheOpenAPIRoutes::class)]
#[CoversClass(CannotCollectRoutes::class)]
#[UsesClass(RouteCollector::class)]
#[UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
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

    #[Test]
    #[DataProviderExternal(ProvidesApiAndRoutes::class, 'defaultBehaviour')]
    public function itCachesRoutes(
        string $apiPath,
        RouteCollection $expected,
    ): void {
        $cachePath = vfsStream::url('root/cache/routes.php');

        self::assertTrue($this->sut->cache($apiPath, $cachePath));

        $actualRouteCollection = eval('?>' . file_get_contents($cachePath));

        self::assertEquals($expected, $actualRouteCollection);
    }

    #[Test]
    #[DataProviderExternal(ProvidesApiAndRoutes::class, 'ignoringServers')]
    public function itCachesRoutesIgnoringServers(
        string $apiPath,
        RouteCollection $expected
    ): void {
        $cachePath = vfsStream::url('root/cache/routes.php');

        self::assertTrue($this->sut->cache($apiPath, $cachePath, true));

        $actual = eval('?>' . file_get_contents($cachePath));

        self::assertEquals($expected, $actual);
    }
}
