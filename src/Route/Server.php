<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Route;

use JsonSerializable;

final class Server implements JsonSerializable
{
    /** @var array<string, Path>*/
    private array $paths = [];

    public function __construct(
        public readonly string $url,
        public readonly string $regex,
    ) {
    }

    public function addRoute(Route $route): void
    {
        if (!isset($this->paths[$route->path])) {
            $this->addPath(new Path($route->path, $route->pathRegex));
        }

        $this->paths[$route->path]->addRoute($route);
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
        return count(array_filter($this->paths, fn($p) => !$p->isEmpty())) === 0;
    }

    public function isHosted(): bool
    {
        return parse_url($this->url, PHP_URL_HOST) !== null;
    }

    /** @return array{
     *          'static': array<string, array<string,string>>,
     *          'dynamic': array{'regex': string, 'paths': array<string, array<string,string>>}
     *          }
     */
    public function jsonSerialize(): array
    {
        $filteredPaths = array_filter($this->paths, fn($p) => !$p->isEmpty());
        usort($filteredPaths, fn($a, $b) => $a->howManyDynamicComponents() <=> $b->howManyDynamicComponents());

        $staticPaths = $dynamicPaths = $regex = [];
        foreach ($filteredPaths as $path) {
            if ($path->isDynamic()) {
                $dynamicPaths[$path->url] = $path->jsonSerialize();
                $regex[] = sprintf('%s(*MARK:%s)', $path->regex, $path->url);
            } else {
                $staticPaths[$path->url] = $path->jsonSerialize();
            }
        }

        return [
            'static' => $staticPaths,
            'dynamic' => [
                'regex' => sprintf('#^(?|%s)$#', implode('|', $regex)),
                'paths' => $dynamicPaths
            ]
        ];
    }

    private function addPath(Path $path): void
    {
        if (!isset($this->paths[$path->url])) {
            $this->paths[$path->url] = $path;
        }
    }
}
