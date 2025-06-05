<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Repository;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use OrderContext\DomainModel\Enum\OrderStatus;
use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;

final readonly class ElasticsearchOrderReadModelRepository implements OrderReadModelRepositoryInterface
{
    public function __construct(
        private Client $client,
        private string $indexName,
    ) {
    }

    public function findById(OrderId $orderId): ?array
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderId->toString(),
            ];

            $response = $this->client->get($params)->asArray();

            if (isset($response['found']) && $response['found']) {
                return $response['_source'];
            }

            return null;
        } catch (ClientResponseException $e) {
            if (404 === $e->getCode()) {
                return null;
            }

            throw new \RuntimeException("Error while searching for order in Elasticsearch: {$e->getMessage()}", 0, $e);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error while searching for order in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    public function findByCustomerId(CustomerId $customerId, int $offset = 0, int $limit = 20): array
    {
        try {
            $params = [
                'index' => $this->indexName,
                'body' => [
                    'query' => [
                        'term' => [
                            'customer_id' => $customerId->toString(),
                        ],
                    ],
                    'sort' => [
                        'created_at' => [
                            'order' => 'desc',
                        ],
                    ],
                    'from' => $offset,
                    'size' => $limit,
                ],
            ];

            $response = $this->client->search($params)->asArray();

            return $this->extractHitsFromResponse($response);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error while searching customer orders in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    public function findByStatus(OrderStatus $status, int $offset = 0, int $limit = 20): array
    {
        try {
            $params = [
                'index' => $this->indexName,
                'body' => [
                    'query' => [
                        'term' => [
                            'status' => $status->value,
                        ],
                    ],
                    'sort' => [
                        'created_at' => [
                            'order' => 'desc',
                        ],
                    ],
                    'from' => $offset,
                    'size' => $limit,
                ],
            ];

            $response = $this->client->search($params)->asArray();

            return $this->extractHitsFromResponse($response);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error when searching orders by status in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    public function count(): int
    {
        try {
            $params = [
                'index' => $this->indexName,
                'body' => [
                    'query' => [
                        'match_all' => new \stdClass(),
                    ],
                ],
            ];

            $response = $this->client->count($params)->asArray();

            return $response['count'] ?? 0;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error while counting orders in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    public function findAll(
        array $filters = [],
        int $page = 1,
        int $limit = 20,
        ?string $sortBy = null,
        string $sortDirection = 'desc',
    ): array {
        try {
            $offset = ($page - 1) * $limit;
            $query = [];

            if (!empty($filters)) {
                $filterClauses = [];

                foreach ($filters as $field => $value) {
                    if (null !== $value && '' !== $value) {
                        if ('status' === $field) {
                            $filterClauses[] = ['term' => [$field => $value]];
                        } elseif ('customer_id' === $field) {
                            $filterClauses[] = ['term' => [$field => $value]];
                        } else {
                            $filterClauses[] = ['match' => [$field => $value]];
                        }
                    }
                }

                if (!empty($filterClauses)) {
                    $query = [
                        'bool' => [
                            'must' => $filterClauses,
                        ],
                    ];
                }
            }

            if (empty($query)) {
                $query = ['match_all' => new \stdClass()];
            }

            $sortField = $sortBy ?: 'created_at';
            $sort = [
                $sortField => [
                    'order' => $sortDirection,
                ],
            ];

            $params = [
                'index' => $this->indexName,
                'body' => [
                    'query' => $query,
                    'sort' => $sort,
                    'from' => $offset,
                    'size' => $limit,
                ],
            ];

            $searchResponse = $this->client->search($params)->asArray();
            $items = $this->extractHitsFromResponse($searchResponse);

            $countParams = [
                'index' => $this->indexName,
                'body' => [
                    'query' => $query,
                ],
            ];

            $countResponse = $this->client->count($countParams)->asArray();
            $total = $countResponse['count'] ?? 0;

            return [
                'items' => $items,
                'total' => $total,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error while searching all orders in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $orderData
     *
     * @throws \RuntimeException
     */
    public function index(array $orderData): void
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderData['order_id'],
                'body' => $orderData,
                'refresh' => true, // Make the document immediately searchable
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error indexing order in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function delete(OrderId $orderId): void
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderId->toString(),
                'refresh' => true,
            ];

            $this->client->delete($params);
        } catch (ClientResponseException $e) {
            if (404 === $e->getCode()) {
                return;
            }

            throw new \RuntimeException("Error deleting order from Elasticsearch: {$e->getMessage()}", 0, $e);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error deleting order from Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function createIndex(): void
    {
        try {
            $indexExists = $this->client->indices()->exists(['index' => $this->indexName])->asBool();
            if ($indexExists) {
                return;
            }

            $params = [
                'index' => $this->indexName,
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'customer_id' => ['type' => 'keyword'],
                            'status' => ['type' => 'keyword'],
                            'total_amount' => [
                                'properties' => [
                                    'amount' => ['type' => 'integer'],
                                    'currency' => ['type' => 'keyword'],
                                ],
                            ],
                            'items' => [
                                'type' => 'nested',
                                'properties' => [
                                    'product_id' => ['type' => 'keyword'],
                                    'quantity' => ['type' => 'integer'],
                                    'price' => [
                                        'properties' => [
                                            'amount' => ['type' => 'integer'],
                                            'currency' => ['type' => 'keyword'],
                                        ],
                                    ],
                                ],
                            ],
                            'created_at' => ['type' => 'date'],
                            'updated_at' => ['type' => 'date'],
                        ],
                    ],
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ];

            $this->client->indices()->create($params);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error creating orders index in Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $response
     *
     * @return array<array<string, mixed>>
     */
    private function extractHitsFromResponse(array $response): array
    {
        $result = [];

        if (isset($response['hits']) && isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                if (isset($hit['_source'])) {
                    $result[] = $hit['_source'];
                }
            }
        }

        return $result;
    }
}
