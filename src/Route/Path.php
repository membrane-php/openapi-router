<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Route;

use JsonSerializable;

final class Path implements JsonSerializable
{
    /** @var array<string, string> */
    private array $operations = [];

    public function __construct(
        public readonly string $url,
        public readonly string $regex,
    ) {
    }

    public function addRoute(Route $route): void
    {
        $this->operations[$route->method] = $route->operationId;
    }

    public function isDynamic(): bool
    {
        return $this->url !== $this->regex;
    }

    public function howManyDynamicComponents(): int
    {
        return substr_count($this->regex, '([^/]+)');
    }

    public function isEmpty(): bool
    {
        return count($this->operations) === 0;
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [...$this->operations];
    }
}
