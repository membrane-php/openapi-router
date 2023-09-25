<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Route;

use Generator;
use Membrane\OpenAPIRouter\Route\Path;
use Membrane\OpenAPIRouter\Route\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Server::class)]
#[UsesClass(Path::class)]
class ServerTest extends TestCase
{
    public static function provideUrls(): array
    {
        return [
            'static server' => ['https://test.com/static', 'https://test.com/static'],
            'partially dynamic server' => ['https://test.com/([^/]+)', 'https://test.com/{dynamic}'],
        ];
    }

    #[Test]
    #[DataProvider('provideUrls')]
    public function itHasARegexBasedOnTheUrl(string $expectedRegex, string $url): void
    {
        self::assertSame($expectedRegex, (new Server($url))->regex);
    }

    public static function provideUrlsToCheckIfDynamic(): array
    {
        return [
            'static path' => [false, 'http://static/server'],
            'partially dynamic path' => [true, 'http://{dynamic}/server'],
            'dynamic path' => [true, 'http://{dynamic}/{server}']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToCheckIfDynamic')]
    public function itCanTellIfItIsDynamic(bool $expected, string $url): void
    {
        self::assertSame($expected, (new Server($url))->isDynamic());
    }

    public static function provideUrlsToCountDynamicComponents(): array
    {
        return [
            'static server' => [0, 'http://static/server'],
            'partially dynamic path' => [1, 'http://{dynamic}/server'],
            'dynamic path' => [2, 'http://{dynamic}/{server}']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToCountDynamicComponents')]
    public function itCanCountDynamicComponents(int $expected, string $url): void
    {
        self::assertSame($expected, (new Server($url))->howManyDynamicComponents());
    }

    #[Test]
    public function itCanAddRoutes(): void
    {
        $sut = new Server('http://server.com');

        self::assertTrue($sut->isEmpty());

        $sut->addRoute('/path', 'get', 'get-operation-id');

        self::assertFalse($sut->isEmpty());
    }

    public static function provideUrlsToCheckIfHosted(): array
    {
        return [
            'hostless' => [false, '/hostless/v1'],
            'hosted' => [true, 'http://www.hosted.io']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToCheckIfHosted')]
    public function itCanTellIfItIsHosted(bool $expected, string $url): void
    {
        self::assertSame($expected, (new Server($url))->isHosted());
    }

    public static function provideServersToJsonSerialize(): Generator
    {
        $expected = [
            'static' => ['/path' => ['get' => 'get-path', 'post' => 'post-path']],
            'dynamic' => [
                'regex' => '#^(?|/([^/]+)/path(*MARK:/{another}/path)|/([^/]+)/([^/]+)/path(*MARK:/{yet}/{another}/path))$#',
                'paths' => [
                    '/{another}/path' => ['get' => 'get-another-path'],
                    '/{yet}/{another}/path' => ['delete' => 'delete-yet-another-path'],
                ],
            ],
        ];

        $paths = [
            ['/path', 'get', 'get-path'],
            ['/path', 'post', 'post-path'],
            ['/{another}/path', 'get', 'get-another-path'],
            ['/{yet}/{another}/path', 'delete', 'delete-yet-another-path'],
        ];



        yield [
            $expected,
            (function () use ($paths) {
                $server = new Server('www.server.net');
                foreach ($paths as $path) {
                    $server->addRoute(...$path);
                }
                return $server;
            })()
        ];
        yield [
            $expected,
            (function () use ($paths) {
                $server = new Server('www.servver.net');
                foreach (array_reverse($paths) as $path) {
                    $server->addRoute(...$path);
                }
                return $server;
            })()
        ];
    }

    #[Test]
    #[DataProvider('provideServersToJsonSerialize')]
    public function itIsJsonSerializable(array $expected, Server $sut): void
    {
        self::assertSame($expected, $sut->jsonSerialize());
    }
}
