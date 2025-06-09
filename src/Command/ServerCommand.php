<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ToolNameEnum;
use App\Factory\ToolFactory;
use App\List\ToolsList;
use InvalidArgumentException;
use Mcp\Server\Server;
use Mcp\Server\ServerRunner;
use Mcp\Types\ListToolsResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:mcp-server',
    description: 'Протестировать mcp',
)]
class ServerCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private ToolsList $toolsList,
        private ToolFactory $toolFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $server = new Server('moex-mcp-server', $this->logger);

        // // Register prompt handlers
        // $server->registerHandler('prompts/list', function ($params) {
        //     $prompt = new Prompt(
        //         name: 'example-prompt',
        //         description: 'An example prompt template',
        //         arguments: [
        //             new PromptArgument(
        //                 name: 'arg1',
        //                 description: 'Example argument',
        //                 required: true
        //             )
        //         ]
        //     );
        //     return new ListPromptsResult([$prompt]);
        // });

        // $server->registerHandler('prompts/get', function (GetPromptRequestParams $params) {

        //     $name = $params->name;
        //     $arguments = $params->arguments;

        //     if ($name !== 'example-prompt') {
        //         throw new \InvalidArgumentException("Unknown prompt: {$name}");
        //     }

        //     // Get argument value safely
        //     $argValue = $arguments ? $arguments->arg1 : 'none';

        //     $prompt = new Prompt(
        //         name: 'example-prompt',
        //         description: 'An example prompt template',
        //         arguments: [
        //             new PromptArgument(
        //                 name: 'arg1',
        //                 description: 'Example argument',
        //                 required: true
        //             )
        //         ]
        //     );

        //     return new GetPromptResult(
        //         messages: [
        //             new PromptMessage(
        //                 role: Role::USER,
        //                 content: new TextContent(
        //                     text: "Example prompt text with argument: $argValue"
        //                 )
        //             )
        //         ],
        //         description: 'Example prompt'
        //     );
        // });

        // Register tool handlers
        $server->registerHandler('tools/list', function ($params) {
            $this->logger->info('tools/list', ['params' => $params]);
            $tools = [];
            foreach ($this->toolsList->get() as $toolItem) {
                $tools[] = $this->toolFactory->create($toolItem)->getTool();
            }

            return new ListToolsResult($tools);
        });

        $server->registerHandler('tools/call', function ($params) {
            $this->logger->info('tools/call', ['params' => $params]);
            $name = ToolNameEnum::tryFrom($params->name);
            if ($name === null) {
                throw new InvalidArgumentException("Unknown tool: {$params->name}");
            }

            $tool = $this->toolFactory->create($name);
            return $tool->__invoke($params->arguments);
        });

        // Create initialization options and run server
        $initOptions = $server->createInitializationOptions();
        $runner = new ServerRunner($server, $initOptions, $this->logger);
        $runner->run();

        return Command::SUCCESS;
    }
}
