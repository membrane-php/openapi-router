<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Console\Service;

use Membrane\OpenAPIReader\Exception\CannotRead;
use Membrane\OpenAPIReader\MembraneReader;
use Membrane\OpenAPIReader\OpenAPIVersion;
use Membrane\OpenAPIRouter\Exception\CannotCollectRoutes;
use Membrane\OpenAPIRouter\RouteCollection;
use Membrane\OpenAPIRouter\RouteCollector;
use Psr\Log\LoggerInterface;

class CacheOpenAPIRoutes
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function cache(
        string $openAPIFilePath,
        string $cacheDestination,
        bool $ignoreServers = false,
        //@todo add support for this in the reader first
        // bool $hostlessFallback,
    ): bool {
        $existingFilePath = $cacheDestination;
        while (!file_exists($existingFilePath)) {
            $existingFilePath = dirname($existingFilePath);
        }
        if (!is_writable($existingFilePath)) {
            $this->logger->error(sprintf('%s cannot be written to', $existingFilePath));
            return false;
        }

        try {
            $openApi = (new MembraneReader([
                OpenAPIVersion::Version_3_0,
                OpenAPIVersion::Version_3_1
            ]))->readFromAbsoluteFilePath($openAPIFilePath);
        } catch (CannotRead $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        if ($ignoreServers) {
            $openApi = $openApi->withoutServers();
        }

        //@todo add support for this in reader first
        // if ($hostlessFallback) {
        //     $openApi = $openApi->withHostlessFallback();
        // }

        try {
            $routeCollection = (new RouteCollector())->collect($openApi);
        } catch (CannotCollectRoutes $e) {
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
