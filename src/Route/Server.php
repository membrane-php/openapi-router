<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Route;

use JsonSerializable;

final class Server implements JsonSerializable
{
    public readonly string $regex;

    /** @var array<string, Path>*/
    private array $paths = [];

    public function __construct(
        public readonly string $url
    ) {
        $regex = preg_replace('#{[^/]+}#', '([^/]+)', $this->url);
        assert(is_string($regex));
        $this->regex = $regex;
    }

    public function addRoute(string $pathUrl, string $method, string $operationId): void
    {
        if (!isset($this->paths[$pathUrl])) {
            $this->paths[$pathUrl] = new Path($pathUrl);
        }

        $this->paths[$pathUrl]->addRoute($method, $operationId);
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
        usort(
            $filteredPaths,
            fn(Path $a, Path $b) => $a->howManyDynamicComponents() <=> $b->howManyDynamicComponents()
        );

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
}
