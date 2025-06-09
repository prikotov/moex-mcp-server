<?php

namespace App\Tool;

use App\Enum\ToolNameEnum;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputProperties;
use Mcp\Types\ToolInputSchema;

class GetSecurityIndicesTool implements ToolInterface
{
    private const string PARAMETER_NAME = 'security';

    public function getName(): string
    {
        return ToolNameEnum::getSecurityIndices->value;
    }

    public function getDescription(): string
    {
        return 'Список индексов в которые входит бумага.';
    }

    public function getTool(): Tool
    {
        $properties = ToolInputProperties::fromArray([
            self::PARAMETER_NAME => [
                'type' => 'string',
                'description' => 'Тикер инструмента'
            ]
        ]);

        $inputSchema = new ToolInputSchema(
            properties: $properties,
            required: [self::PARAMETER_NAME]
        );

        return new Tool(
            name: $this->getName(),
            inputSchema: $inputSchema,
            description: $this->getDescription()
        );
    }

    public function __invoke(mixed ...$args): CallToolResult
    {
        $params = $args[0] ?? [];

        if (!is_array($params) || !isset($params[self::PARAMETER_NAME])) {
            return new CallToolResult(
                content: [new TextContent(
                    text: "Missing parameter: " . self::PARAMETER_NAME . "."
                )],
                isError: true
            );
        }
        $security = $params[self::PARAMETER_NAME];
        if (empty($security)) {
            return new CallToolResult(
                content: [new TextContent(
                    text: "Error: " . self::PARAMETER_NAME . " cannot be empty"
                )],
                isError: true
            );
        }

        $content = file_get_contents(sprintf(
            "https://iss.moex.com/iss/securities/%s/indices.json?iss.meta=off&iss.only=description&iss.json=extended&only_actual=1",
            $security
        ));

        return new CallToolResult(
            content: [new TextContent(
                text: $content
            )]
        );
    }
}
