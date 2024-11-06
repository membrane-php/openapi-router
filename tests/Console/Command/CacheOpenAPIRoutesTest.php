<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Console\Command;

use Membrane\OpenAPIRouter\Console\Command\CacheOpenAPIRoutes;
use Membrane\OpenAPIRouter\Console\Service;
use Membrane\OpenAPIRouter\Exception;
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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CacheOpenAPIRoutes::class)]
#[CoversClass(Exception\CannotCollectRoutes::class)]
#[UsesClass(Service\CacheOpenAPIRoutes::class)]
#[UsesClass(Route\Server::class), UsesClass(Route\Path::class)]
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
        file_put_contents($openAPIFilePath, json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => '', 'version' => '1.0.0'],
            'paths' => []
        ]));

        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute([
            'openAPI' => $openAPIFilePath,
            'destination' => vfsStream::url('cache/routes.php'),
        ]);

        self::assertSame(Command::FAILURE, $sut->getStatusCode());
    }

    #[Test]
    #[DataProviderExternal(ProvidesApiAndRoutes::class, 'defaultBehaviour')]
    public function itCachesRoutes(string $openAPI, RouteCollection $expected): void
    {
        $cachePath = vfsStream::url('cache/routes.php');

        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute(['openAPI' => $openAPI, 'destination' => $cachePath]);

        self::assertSame(Command::SUCCESS, $sut->getStatusCode());

        $actual = eval('?>' . file_get_contents($cachePath));

        self::assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProviderExternal(ProvidesApiAndRoutes::class, 'ignoringServers')]
    public function itCachesRoutesIgnoringServers(
        string $openAPI,
        RouteCollection $expected,
    ): void {
        $cachePath = vfsStream::url('cache/routes.php');

        $sut = new CommandTester(new CacheOpenAPIRoutes());

        $sut->execute([
            'openAPI' => $openAPI,
            'destination' => $cachePath,
            '--ignore-servers' => true,
        ]);

        self::assertSame(Command::SUCCESS, $sut->getStatusCode());

        $actual = eval('?>' . file_get_contents($cachePath));

        self::assertEquals($expected, $actual);
    }
}
