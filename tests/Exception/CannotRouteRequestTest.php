<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Exception;

use Generator;
use Membrane\OpenAPIRouter\Exception\CannotRouteRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CannotRouteRequest::class)]
class CannotRouteRequestTest extends TestCase
{
    public static function provideErrorCodes(): Generator
    {
        yield '404' => [404, CannotRouteRequest::notFound()];
        yield '405' => [405, CannotRouteRequest::methodNotAllowed()];
    }

    #[Test]
    #[DataProvider('provideErrorCodes')]
    public function itConstructsFromErrorCodes(int $errorCode, CannotRouteRequest $expectedException): void
    {
        $actualException = CannotRouteRequest::fromErrorCode($errorCode);

        self::assertEquals($expectedException, CannotRouteRequest::fromErrorCode($errorCode));
        self::assertSame($errorCode, $actualException->getCode());
    }
}
