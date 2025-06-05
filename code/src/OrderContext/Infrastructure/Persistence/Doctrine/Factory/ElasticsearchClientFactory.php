<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Factory;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

final readonly class ElasticsearchClientFactory
{
    /**
     * @param LoggerInterface|null $logger Optional logger for Elasticsearch client
     */
    public function __construct(
        private ?LoggerInterface $logger = null,
        private string $elasticsearchHost,
        private int $elasticsearchRetries,
    ) {
    }

    public function createClient(): Client
    {
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts([$this->elasticsearchHost]);

        if (null !== $this->logger) {
            $clientBuilder->setLogger($this->logger);
        }

        $clientBuilder->setRetries($this->elasticsearchRetries);
        $clientBuilder->setSSLVerification(false);

        return $clientBuilder->build();
    }
}
