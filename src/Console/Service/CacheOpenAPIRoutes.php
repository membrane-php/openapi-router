<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Console\Service;

use Membrane\OpenAPIRouter\Exception\CannotProcessOpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotReadOpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotRouteOpenAPI;
use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection;
use Psr\Log\LoggerInterface;

class CacheOpenAPIRoutes
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function cache(string $openAPIFilePath, string $cacheDestination): bool
    {
        $existingFilePath = $cacheDestination;
        while (!file_exists($existingFilePath)) {
            $existingFilePath = dirname($existingFilePath);
        }
        if (!is_writable($existingFilePath)) {
            $this->logger->error(sprintf('%s cannot be written to', $existingFilePath));
            return false;
        }

        try {
            $openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath($openAPIFilePath);
        } catch (CannotReadOpenAPI $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        try {
            $routeCollection = (new RouteCollector())->collect($openApi);
        } catch (CannotRouteOpenAPI | CannotProcessOpenAPI $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        $routes = sprintf(
            '<?php return new %s(%s);',
            RouteCollection::class,
            var_export($routeCollection->routes, true)
        );


        if (!file_exists(dirname($cacheDestination))) {
            mkdir(dirname($cacheDestination), recursive: true);
        }
        file_put_contents($cacheDestination, $routes);

        return true;
    }
}
