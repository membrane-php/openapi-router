<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Exception;

/* This exception occurs when a route collection cannot be created from your OpenAPI */

class CannotRouteOpenAPI extends \RuntimeException
{
    public const NO_ROUTES = 0;

    public static function noRoutes(): self
    {
        return new self('No routes found in OpenAPI specification', self::NO_ROUTES);
    }
}
