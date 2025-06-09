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
    private const string PARAMETER_SECURITY = 'security';

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
            self::PARAMETER_SECURITY => [
                'type' => 'string',
                'description' => 'Тикер инструмента'
            ]
        ]);

        $inputSchema = new ToolInputSchema(
            properties: $properties,
            required: [self::PARAMETER_SECURITY]
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

        if (!is_array($params) || !isset($params[self::PARAMETER_SECURITY])) {
            return new CallToolResult(
                content: [new TextContent(
                    text: "Missing parameter: " . self::PARAMETER_SECURITY . "."
                )],
                isError: true
            );
        }
        $security = $params[self::PARAMETER_SECURITY];
        if (empty($security)) {
            return new CallToolResult(
                content: [new TextContent(
                    text: 'Error: "' . self::PARAMETER_SECURITY . '" cannot be empty.'
                )],
                isError: true
            );
        }

        $data = [
            'iss.meta' => 'off',
            'iss.json' => 'extended',
            'only_actual' => 1,
        ];
        $url = sprintf(
            "https://iss.moex.com/iss/securities/%s/indices.json?%s",
            $security,
            http_build_query($data)
        );
        $content = @file_get_contents($url);
        if ($content === false) {
            return new CallToolResult(
                content: [new TextContent(text: "Error: Unable to fetch data from ISS: " . $url)],
                isError: true
            );
        }

        return new CallToolResult(
            content: [new TextContent(
                text: $content
            )]
        );
    }
}
