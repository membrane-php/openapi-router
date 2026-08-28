<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter;

class RouteMatch
{
    public function __construct(
        public readonly string $operationId,
        public readonly string $source,
    ) {
    }
}
