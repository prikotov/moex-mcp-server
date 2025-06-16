<?php

declare(strict_types=1);

namespace App\Command;

use Mcp\Client\Client;
use Mcp\Client\Transport\StdioServerParameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mcp-client',
    description: 'Протестировать MCP',
)]
class ClientCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'via',
            null,
            InputOption::VALUE_REQUIRED,
            'Launch server via: console, podman, docker',
            'console'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $via = $input->getOption('via');
        switch ($via) {
            case 'podman':
                $serverParams = new StdioServerParameters(
                    command: 'podman',
                    args: [
                        'run',
                        '--rm',
                        '-i',
                        'moex-mcp-server',
                        'bin/server',
                    ],
                );
                break;
            case 'docker':
                $serverParams = new StdioServerParameters(
                    command: 'docker',
                    args: [
                        'run',
                        '--rm',
                        '-i',
                        'moex-mcp-server',
                        'bin/server',
                    ],
                );
                break;
            case 'console':
            default:
                $serverParams = new StdioServerParameters(
                    command: 'bin/server',
                );
        }

        $io->title('MCP Client Test');

        // Create client instance
        $client = new Client($this->logger);

        try {
            $io->info('Connecting to MCP server...');
            // Connect to the server using stdio transport
            $session = $client->connect(
                commandOrUrl: $serverParams->getCommand(),
                args: $serverParams->getArgs(),
                env: $serverParams->getEnv(),
            );

            $io->info('Fetching list of available tools...');
            $toolsResult = $session->listTools();
            if (!empty($toolsResult->tools)) {
                $io->info('Available tools:');
                foreach ($toolsResult->tools as $tool) {
                    $output->writeln('  - Name: ' . $tool->name);
                    $output->writeln('    Description: ' . $tool->description);
                    $output->writeln('    Arguments:');
                    $output->writeln('      - ' . json_encode($tool->inputSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }
            } else {
                $io->warning('No tools available.');
            }

            $io->info('Calling tool: get_security_specification');
            $toolsResult = $session->callTool('get_security_specification', [
                'security' => 'SBER',
            ]);
            $output->writeln(json_encode(json_decode($toolsResult->content[0]->text, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));


            $io->info('Calling tool: get_security_indices');
            $toolsResult = $session->callTool('get_security_indices', [
                'security' => 'SBER',
            ]);
            $output->writeln(json_encode(json_decode($toolsResult->content[0]->text, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $io->info('Calling tool: get_security_aggregates');
            $toolsResult = $session->callTool('get_security_aggregates', [
                'security' => 'SBER',
                'date' => '2025-06-06',
            ]);
            $output->writeln(json_encode(json_decode($toolsResult->content[0]->text, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $io->info('Calling tool: get_security_trade_data');
            $toolsResult = $session->callTool('get_security_trade_data', [
                'security' => 'SBER',
            ]);
            $output->writeln(json_encode(json_decode($toolsResult->content[0]->text, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));


        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            // Close the server connection
            $client->close();
            $io->info('Close the server connection.');
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
