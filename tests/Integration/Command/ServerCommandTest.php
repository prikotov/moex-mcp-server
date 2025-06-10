<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use Mcp\Client\Client;
use Mcp\Client\ClientSession;
use Mcp\Client\Transport\StdioServerParameters;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServerCommandTest extends KernelTestCase
{
    private ClientSession $clientSession;

    private function startSession(): ClientSession
    {
        if (!isset($this->clientSession)) {
            $serverParams = new StdioServerParameters(
                command: 'bin/console',
                args: [
                    'app:mcp-server',
                ],
            );
            $client = new Client();

            $this->clientSession = $client->connect(
                commandOrUrl: $serverParams->getCommand(),
                args: $serverParams->getArgs(),
                env: $serverParams->getEnv(),
            );
        }

        return $this->clientSession;
    }

    public function testListTools(): void {
        $session = $this->startSession();

        $toolsResult = $session->listTools();
        $toolsAsJson = json_encode($toolsResult, JSON_UNESCAPED_UNICODE);

        $this->assertStringContainsString('get_security_specification', $toolsAsJson);
        $this->assertStringContainsString('get_security_indices', $toolsAsJson);
        $this->assertStringContainsString('get_security_aggregates', $toolsAsJson);
        $this->assertStringContainsString('get_security_trade_data', $toolsAsJson);
    }

    public function testGetSecuritySpecification(): void
    {
        $session = $this->startSession();
        $res = $session->callTool('get_security_specification', [
            'security' => 'SBER',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('ISIN код', $resAsJson);
        $this->assertStringContainsString('RU0009029540', $resAsJson);
    }

    public function testGetSecurityIndices(): void
    {
        $session = $this->startSession();
        $res = $session->callTool('get_security_indices', [
            'security' => 'SBER',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('Индекс финансов', $resAsJson);
        $this->assertStringContainsString('MOEXFN', $resAsJson);
    }

    public function testGetSecurityAggregates(): void
    {
        $session = $this->startSession();
        $res = $session->callTool('get_security_aggregates', [
            'security' => 'SBER',
            'date' => '2025-06-06',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('market_name', $resAsJson);
        $this->assertStringContainsString('shares', $resAsJson);
        $this->assertStringContainsString('2025-06-06', $resAsJson);

        $res = $session->callTool('get_security_aggregates', [
            'security' => 'SBER',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('market_name', $resAsJson);
        $this->assertStringContainsString('shares', $resAsJson);

        $res = $session->callTool('get_security_aggregates', [
            'security' => 'SBER',
            'date' => '1800-06-06',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('market_name', $resAsJson);
        $this->assertStringNotContainsString('shares', $resAsJson);

        $res = $session->callTool('get_security_aggregates', [
            'security' => 'SBER',
            'date' => '2066-66-66',
        ]);
        $this->assertTrue($res->isError);
        $this->assertStringContainsString('Error', $res->content[0]->text);
        $this->assertStringContainsString('is not a valid date', $res->content[0]->text);
    }

    public function testGetSecurityTradeData(): void
    {
        $session = $this->startSession();
        $res = $session->callTool('get_security_trade_data', [
            'security' => 'SBER',
        ]);
        $this->assertFalse($res->isError);
        $resAsJson = json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('securities', $resAsJson);
        $this->assertStringContainsString('marketdata', $resAsJson);
        $this->assertStringContainsString('Сбербанк', $resAsJson);
    }
}
