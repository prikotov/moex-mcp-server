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

class GetSecurityAggregatesTool implements ToolInterface
{
    private const string PARAMETER_SECURITY = 'security';
    private const string PARAMETER_DATE = 'date';

    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    public function getName(): string
    {
        return ToolNameEnum::getSecurityAggregates->value;
    }

    public function getDescription(): string
    {
        return 'Агрегированные итоги торгов за дату по рынкам.';
    }

    public function getTool(): Tool
    {
        $properties = ToolInputProperties::fromArray([
            self::PARAMETER_SECURITY => [
                'type' => 'string',
                'description' => 'Тикер инструмента'
            ],
            self::PARAMETER_DATE => [
                'type' => 'string',
                'description' => 'Дата за которую необходимо отобразить данные. По умолчанию за последнюю дату в итогах торгов. Формат даты YYYY-MM-DD.'
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

        $query = [
            'iss.only' => 'aggregates',
        ];
        $date = $params[self::PARAMETER_DATE] ?? null;
        if ($date !== null) {
            $normalizeDate = $this->normalizeDate($date);
            if ($normalizeDate === null) {
                return new CallToolResult(
                    content: [new TextContent(
                        text: 'Error: "' . self::PARAMETER_DATE . '" is not a valid date.'
                    )],
                    isError: true
                );
            }
            $query['date'] = $normalizeDate;
        }

        try {
            $content = $this->moexComponent->getContent(
                "https://iss.moex.com/iss/securities/%s/aggregates",
                urlData: [$security],
                query: $query
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

    private function normalizeDate(string $date): ?string
    {
        $dt = date_create_immutable($date);
        return $dt === false ? null : $dt->format('Y-m-d');
    }
}
