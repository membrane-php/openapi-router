<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Exception;

/* This exception occurs when the request does not match any routes in your route collection. */

use RuntimeException;

class CannotRouteRequest extends RuntimeException
{
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;

    public static function fromErrorCode(int $errorCode): self
    {
        assert($errorCode === 404 || $errorCode === 405);
        return match ($errorCode) {
            self::NOT_FOUND => self::notFound(),
            self::METHOD_NOT_ALLOWED => self::methodNotAllowed(),
        };
    }

    public static function notFound(): self
    {
        return new self('not found', 404);
    }

    public static function methodNotAllowed(): self
    {
        return new self('method not allowed', 405);
    }
}
