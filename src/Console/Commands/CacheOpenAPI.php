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
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'membrane:router:generate-routes',
    description: 'Parses OpenAPI file to write a cached set of routes to the given file',
)]
class CacheOpenAPI extends Command
{
    protected function configure(): void
    {
        self::addArgument(
            'openAPI',
            InputArgument::REQUIRED,
            'The absolute filepath to your OpenAPI'
        );
        self::addArgument(
            'destination',
            InputArgument::OPTIONAL,
            'The filepath for the generated route collection',
            getcwd() . '/cache/routes.php'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $openAPIFilePath = $input->getArgument('openAPI');
        assert(is_string($openAPIFilePath));
        $existingFilePath = $destination = $input->getArgument('destination');
        assert(is_string($existingFilePath) && is_string($destination));

        while (!file_exists($existingFilePath)) {
            $existingFilePath = dirname($existingFilePath);
        }
        if (!is_writable($existingFilePath)) {
            $this->outputErrorBlock(sprintf('%s cannot be written to', $existingFilePath), $output);
            return Command::FAILURE;
        }

        try {
            $openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath($openAPIFilePath);
        } catch (CannotReadOpenAPI $e) {
            $this->outputErrorBlock($e->getMessage(), $output);
            return Command::FAILURE;
        }

        try {
            $routeCollection = (new RouteCollector())->collect($openApi);
        } catch (CannotRouteOpenAPI | CannotProcessOpenAPI $e) {
            $this->outputErrorBlock($e->getMessage(), $output);
            return Command::FAILURE;
        }

        $routes = sprintf(
            '<?php return new %s(%s);',
            RouteCollection::class,
            var_export($routeCollection->routes, true)
        );


        mkdir(dirname($destination), recursive: true);
        file_put_contents($destination, $routes);

        return Command::SUCCESS;
    }

    private function outputErrorBlock(string $message, OutputInterface $output): void
    {
        $formattedMessage = (new FormatterHelper())->formatBlock($message, 'error', true);
        $output->writeLn(sprintf("\n%s\n", $formattedMessage));
    }
}
