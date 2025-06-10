<?php

declare(strict_types=1);

namespace App\Component;

use App\Exception\InfrastructureExceptionInterface;

interface MoexIssComponentInterface
{
    /**
     * @throws InfrastructureExceptionInterface
     */
    public function getContent(string $url, array $urlData, array $query): ?string;
}
