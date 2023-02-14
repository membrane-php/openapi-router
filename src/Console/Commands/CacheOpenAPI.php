<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Console\Commands;

use Membrane\OpenAPIRouter\Exception\CannotProcessOpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotReadOpenAPI;
use Membrane\OpenAPIRouter\Exception\CannotRouteOpenAPI;
use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;
use Membrane\OpenAPIRouter\Router\Collector\RouteCollector;
use Membrane\OpenAPIRouter\Router\ValueObject\RouteCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'membrane:router:generate-routes',
    description: 'Parses OpenAPI file to write a cached set of routes to the given file',
)]
class CacheOpenAPI extends Command
{
    protected function configure()
    {
        self::addArgument(
            'openAPI',
            InputArgument::REQUIRED,
            'The absolute filepath to your OpenAPI'
        );
        self::addArgument(
            'destination',
            InputArgument::OPTIONAL,
            'The absolute filepath for the generated route collection',
            __DIR__ . '/../../../cache/routes.php'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $openAPIFilePath = $input->getArgument('openAPI');
        $destination = $input->getArgument('destination');

        try {
            assert(is_string($openAPIFilePath));
            $openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath($openAPIFilePath);
        } catch (CannotReadOpenAPI $e) {
            $output->writeln($e->getMessage());
            return Command::INVALID;
        }

        assert(is_string($destination));
        if (is_writable($destination)) {
            echo sprintf('%s is an invalid filename', $destination);
            return Command::INVALID;
        }

        try {
            $routeCollection = (new RouteCollector())->collect($openApi);
        } catch (CannotRouteOpenAPI | CannotProcessOpenAPI $e) {
            $output->writeln($e->getMessage());
            return Command::INVALID;
        }

        $routes = sprintf(
            '<?php return new %s(%s);',
            RouteCollection::class,
            var_export($routeCollection->routes, true)
        );
        file_put_contents($destination, $routes);

        return Command::SUCCESS;
    }
}
