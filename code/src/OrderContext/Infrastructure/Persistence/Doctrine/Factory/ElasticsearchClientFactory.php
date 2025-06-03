<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Factory;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating Elasticsearch client
 */
final readonly class ElasticsearchClientFactory
{
    /**
     * @param LoggerInterface|null $logger Optional logger for Elasticsearch client
     */
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Creates and configures an Elasticsearch client
     *
     * @return Client
     */
    public function createClient(): Client
    {
        $hosts = $_ENV['ELASTICSEARCH_URL'] ?? 'http://elasticsearch:9200';
        
        // Create client builder
        $builder = ClientBuilder::create();
        
        // Configure hosts (can be array or string)
        $builder->setHosts([$hosts]);
        
        // Add logger if provided
        if ($this->logger !== null) {
            $builder->setLogger($this->logger);
        }
        
        // Set number of retries
        $builder->setRetries((int)($_ENV['ELASTICSEARCH_RETRIES'] ?? 2));
        
        // Build and return the client
        return $builder->build();
    }
}
