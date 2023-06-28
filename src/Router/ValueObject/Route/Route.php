<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Router\ValueObject\Route;

class Route
{
    public function __construct(
        public readonly string $path,
        public readonly string $pathRegex,
        public readonly string $method,
        public readonly string $operationId
    ) {
    }
}
