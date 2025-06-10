<?php

declare(strict_types=1);

namespace App\Component;

use App\Exception\InfrastructureException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MoexIssComponent implements MoexIssComponentInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
        $this->httpClient = HttpClient::create([
            'headers' => [
                'timeout' => 10,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getContent(string $url, array $urlData, array $query): ?string
    {
        $url = sprintf(
            $url,
            ...$urlData
        );
        $query['iss.json'] = 'extended';
        $query['iss.meta'] = 'off';
        $url = sprintf(
            $url . ".json?%s",
            http_build_query($query)
        );

        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
            );
            $content = $response->getContent();
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error("Failed to get data from MOEX ISS: " . $e->getMessage(), [
                'url' => $url,
                'code' => $e->getCode(),
            ]);
            throw new InfrastructureException(
                message: sprintf(
                    'Failed to get data from MOEX ISS (url: %s): %s',
                    $url,
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (empty($content)) {
            $this->logger->error("Empty response from MOEX ISS", [
                'url' => $url,
            ]);
            throw new InfrastructureException(sprintf(
                'Empty response from MOEX ISS (url: %s)',
                $url
            ));
        }

        $this->logger->info("Data fetched from MOEX ISS", [
            'url' => $url,
            'length' => strlen($content),
            'content' => $content,
        ]);

        return $content;
    }
}
