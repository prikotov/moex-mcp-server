<?php

declare(strict_types=1);

namespace App\List;

use App\Enum\ToolNameEnum;

class ToolsList
{
    /**
     * @return ToolNameEnum[]
     */
    public function get(): array
    {
        return [
            ToolNameEnum::getSecuritySpecification,
            ToolNameEnum::getSecurityIndices,
            ToolNameEnum::getSecurityAggregates,
            ToolNameEnum::getSecurityTradeData,
        ];
    }
}
