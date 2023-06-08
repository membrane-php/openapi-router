<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'membrane:router:generate-routes',
    description: 'Parses OpenAPI file to write a cached set of routes to the given file',
)]
class CacheOpenAPIRoutes extends Command
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
        $destination = $input->getArgument('destination');
        assert(is_string($destination));

        $logger = new ConsoleLogger($output);
        $service = new \Membrane\OpenAPIRouter\Console\Service\CacheOpenAPIRoutes($logger);

        return $service->cache($openAPIFilePath, $destination) ? Command::SUCCESS : Command::FAILURE;
    }
}
