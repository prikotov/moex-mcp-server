<?php

declare(strict_types=1);

namespace App\Command;

use Mcp\Client\Client;
use Mcp\Client\Transport\StdioServerParameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $serverParams = new StdioServerParameters(
//            command: 'bin/console',
//            args: [
//                'app:mcp-server',
//            ],
//        );
        $serverParams = new StdioServerParameters(
            command: 'podman',
            args: [
                'run',
                '--rm',
                '-i',
                'moex-mcp-server',
                'bin/console',
                'app:mcp-server',
            ],
        );

        // Create client instance
        $client = new Client($this->logger);

        try {
            echo("Starting to connect\n");
            // Connect to the server using stdio transport
            $session = $client->connect(
                commandOrUrl: $serverParams->getCommand(),
                args: $serverParams->getArgs(),
                env: $serverParams->getEnv(),
            );

            //$toolsResult = $session->callTool();

            echo("Starting to get available prompts\n");
            $toolsResult = $session->listTools();
            if (!empty($toolsResult->tools)) {
                echo "Available tools:\n";
                foreach ($toolsResult->tools as $tool) {
                    echo "  - Name: " . $tool->name . "\n";
                    echo "    Description: " . $tool->description . "\n";
                    echo "    Arguments:\n";
                    echo "      - " . json_encode($tool->inputSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
//                    if (!empty($tool->inputSchema->properties)) {
//                        foreach ($tool->inputSchema->properties as $property) {
//                            echo "      - " . $property . " (" . ($argument->required ? "required" : "optional") . "): " . $argument->description . "\n";
//                        }
//                    } else {
//                        echo "      (None)\n";
//                    }
                }
            } else {
                echo "No prompts available.\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        } finally {
            // Close the server connection
            if (isset($client)) {
                $client->close();
                echo "Close the server connection.\n";
            }
        }

        echo "Done.\n";

        return Command::SUCCESS;
    }
}
