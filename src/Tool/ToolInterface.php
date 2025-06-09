<?php

namespace App\Tool;

use Mcp\Types\CallToolResult;
use Mcp\Types\Tool;

interface ToolInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getTool(): Tool;

    public function __invoke(mixed ...$args): CallToolResult;
}
