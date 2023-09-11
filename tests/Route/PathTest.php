<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Route;

use Generator;
use Membrane\OpenAPIRouter\Route\Path;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Path::class)]
class PathTest extends TestCase
{
    public static function provideUrlsToBaseRegexOn(): array
    {
        return [
            'static path' => ['/static/path', '/static/path'],
            'partially dynamic path' => ['/([^/]+)/path', '/{dynamic}/path'],
            'dynamic path' => ['/([^/]+)/([^/]+)', '/{dynamic}/{path}']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToBaseRegexOn')]
    public function itHasARegexBasedOnTheUrl(string $expected, string $url): void
    {
        self::assertSame($expected, (new Path($url))->regex);
    }

    public static function provideUrlsToCheckIfDynamic(): array
    {
        return [
            'static path' => [false, '/static/path'],
            'partially dynamic path' => [true, '/{dynamic}/path'],
            'dynamic path' => [true, '/{dynamic}/{path}']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToCheckIfDynamic')]
    public function itCanTellIfItIsDynamic(bool $expected, string $url): void
    {
        self::assertSame($expected, (new Path($url))->isDynamic());
    }

    public static function provideUrlsToCountDynamicComponents(): array
    {
        return [
            'static path' => [0, '/static/path'],
            'partially dynamic path' => [1, '/{dynamic}/path'],
            'dynamic path' => [2, '/{dynamic}/{path}']
        ];
    }

    #[Test]
    #[DataProvider('provideUrlsToCountDynamicComponents')]
    public function itCanCountDynamicComponents(int $expected, string $url): void
    {
        self::assertSame($expected, (new Path($url))->howManyDynamicComponents());
    }

    #[Test]
    public function itCanAddRoutes(): void
    {
        $sut = new Path('/path');

        self::assertTrue($sut->isEmpty());

        $sut->addRoute('get', 'get-operation-id');

        self::assertFalse($sut->isEmpty());
    }

    public static function providePathsToJsonSerialize(): Generator
    {
        $expected = [
            'delete' => 'delete-operation',
            'get' => 'get-operation',
            'post' => 'post-operation'
        ];

        $operations = [
          ['get', 'get-operation'],
          ['post', 'post-operation'],
          ['delete', 'delete-operation'],
        ];

        yield [
            $expected,
            (function() use ($operations) {
                $sut = new Path('/path');
                foreach ($operations as $operation) {
                    $sut->addRoute(...$operation);
                }
                return $sut;
            })(),
        ];
        yield [
            $expected,
            (function() use ($operations) {
                $sut = new Path('/path');
                foreach (array_reverse($operations) as $operation) {
                    $sut->addRoute(...$operation);
                }
                return $sut;
            })(),
        ];
    }

    #[Test]
    public function itIsJsonSerializable(): void
    {
        $sut = new Path('/path');

        $sut->addRoute('get', 'get-operation-id');
        $sut->addRoute('post', 'post-operation-id');

        self::assertSame(['get' => 'get-operation-id', 'post' => 'post-operation-id'], $sut->jsonSerialize());
    }
}
