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

class GetSecurityTradeDataTool implements ToolInterface
{
    private const string PARAMETER_SECURITY = 'security';

    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    public function getName(): string
    {
        return ToolNameEnum::getSecurityTradeData->value;
    }

    public function getDescription(): string
    {
        return 'Вернуть текущие рыночные данные по инструменту на фондовом рынке Московской биржи.';
    }

    public function getTool(): Tool
    {
        $properties = ToolInputProperties::fromArray([
            self::PARAMETER_SECURITY => [
                'type' => 'string',
                'description' => 'Тикер инструмента'
            ],
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

        /** @link https://iss.moex.com/iss/engines/stock/markets/shares/securities/columns - Описание полей */
        try {
            $content = $this->moexComponent->getContent(
                url: "https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities/%s",
                urlData: [$security],
                query: [
                    'iss.only' => 'securities,marketdata',
                    'securities.columns' => 'BOARDID,BOARDNAME,SECID,SHORTNAME,SECNAME,PREVPRICE,PREVDATE,PREVLEGALCLOSEPRICE',
                    'marketdata.columns' => 'SECID,BOARDID,OPEN,LOW,HIGH,LAST,VALTODAY,TIME,SYSTIME',
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
