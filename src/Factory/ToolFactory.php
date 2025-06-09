<?php

namespace App\Factory;

use App\Enum\ToolNameEnum;
use App\Tool\ToolInterface;
use InvalidArgumentException;

readonly class ToolFactory
{
    /**
     * @param ToolInterface[] $tools
     */
    public function __construct(
        private array $tools = [],
    ) {
    }

    public function create(ToolNameEnum $name): ToolInterface
    {
        $tool = $this->tools[$name->value] ?? null;
        if ($tool === null) {
            throw new InvalidArgumentException(sprintf("Tool %s unknown", $name->value));
        }
        return $tool;
    }
}
