<?php

declare(strict_types=1);

namespace App\Enum;

enum ToolNameEnum: string
{
    case getSecuritySpecification = 'get_security_specification';
    case getSecurityIndices = 'get_security_indices';
    case getSecurityAggregates = 'get_security_aggregates';
}
