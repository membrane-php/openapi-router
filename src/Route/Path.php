<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Route;

use JsonSerializable;

final class Path implements JsonSerializable
{
    public readonly string $regex;
    /** @var array<string, string> */
    private array $operations = [];

    public function __construct(
        public readonly string $url
    ) {
        $regex = preg_replace('#{[^/]+}#', '([^/]+)', $this->url);
        assert(is_string($regex));
        $this->regex = $regex;
    }

    public function addRoute(string $method, string $operationId): void
    {
        $this->operations[$method] = $operationId;
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
