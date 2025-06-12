<?php

namespace App\Tool;

use App\Component\MoexIssComponentInterface;
use App\Enum\ToolNameEnum;
use App\Exception\InfrastructureExceptionInterface;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputProperties;
use Mcp\Types\ToolInputSchema;

class GetSecuritySpecificationTool implements ToolInterface
{
    private const string PARAMETER_SECURITY = 'security';

    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    public function getName(): string
    {
        return ToolNameEnum::getSecuritySpecification->value;
    }

    public function getDescription(): string
    {
        return 'Вернуть спецификацию указанного инструмента из ISS МОEX.';
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

        try {
            $content = $this->moexComponent->getContent(
                "https://iss.moex.com/iss/securities/%s",
                urlData: [$security],
                query: [
                    'iss.only' => 'description',
                ]
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new CallToolResult(
                content: [new TextContent(text: "Unable to fetch data from MOEX: " . $e->getMessage())],
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
