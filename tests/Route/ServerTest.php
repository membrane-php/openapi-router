<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Route;

use Membrane\OpenAPIRouter\Route\Server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Server::class)]
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
    public function itCreatesARegexBasedOnTheUrl(string $expectedRegex, string $url): void
    {
        self::assertSame($expectedRegex, (new Server($url))->regex);
    }
}
