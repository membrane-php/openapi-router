<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Console\Command;

use Membrane\OpenAPIRouter\Console\Service;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->setDefinition(new InputDefinition([
            new InputArgument(
                name: 'openAPI',
                mode: InputArgument::REQUIRED,
                description: 'The absolute filepath to your OpenAPI'
            ),
            new InputArgument(
                name: 'destination',
                mode: InputArgument::OPTIONAL,
                description: 'The filepath for the generated route collection',
                default: getcwd() . '/cache/routes.php'
            ),
            new InputOption(
                name: 'ignore-servers',
                description: 'ignore servers, only use the default "/" server',
                mode: InputOption::VALUE_NONE,
            ),
            // @todo add support for this in the reader first
            // new InputOption(
            //     name: 'with-hostless-fallback',
            //     description: 'add the default "/" server, if not already specified',
            // ),
        ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $openAPIFilePath = $input->getArgument('openAPI');
        assert(is_string($openAPIFilePath));

        $destination = $input->getArgument('destination');
        assert(is_string($destination));

        $ignoreServers = $input->getOption('ignore-servers');
        assert(is_bool($ignoreServers));
        // @todo add support this in the reader first
        // $hostlessFallback = $input->getOption('with-hostless-fallback');

        $logger = new ConsoleLogger($output);

        return (new Service\CacheOpenAPIRoutes($logger))->cache(
            $openAPIFilePath,
            $destination,
            $ignoreServers,
            // @todo add support this in the reader first
            // $hostlessFallback,
        ) ? Command::SUCCESS : Command::FAILURE;
    }
}
