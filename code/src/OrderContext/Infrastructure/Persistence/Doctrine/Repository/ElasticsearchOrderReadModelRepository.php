<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Repository;

use Elasticsearch\Client;
use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;
use RuntimeException;

/**
 * Реализация репозитория для чтения данных о заказах через Elasticsearch
 */
final readonly class ElasticsearchOrderReadModelRepository implements OrderReadModelRepositoryInterface
{
    /**
     * @param Client $client Клиент Elasticsearch
     * @param string $indexName Имя индекса в Elasticsearch
     */
    public function __construct(
        private Client $client,
        private string $indexName = 'orders'
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findById(OrderId $orderId): ?array
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderId->toString(),
            ];

            $response = $this->client->get($params);
            
            if (isset($response['found']) && $response['found']) {
                return $response['_source'];
            }
            
            return null;
        } catch (\Exception $e) {
            // Если документ не найден, Elasticsearch выбрасывает исключение
            if (strpos($e->getMessage(), '404 Not Found') !== false) {
                return null;
            }
            
            throw new RuntimeException("Ошибка при поиске заказа в Elasticsearch: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
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

            $response = $this->client->search($params);
            
            return $this->extractHitsFromResponse($response);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при поиске заказов клиента в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
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

            $response = $this->client->search($params);
            
            return $this->extractHitsFromResponse($response);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при поиске заказов по статусу в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
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

            $response = $this->client->count($params);
            
            return $response['count'] ?? 0;
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при подсчете заказов в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findAll(int $offset = 0, int $limit = 20): array
    {
        try {
            $params = [
                'index' => $this->indexName,
                'body' => [
                    'query' => [
                        'match_all' => new \stdClass(),
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

            $response = $this->client->search($params);
            
            return $this->extractHitsFromResponse($response);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при поиске всех заказов в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Индексирует или обновляет заказ в Elasticsearch
     *
     * @param array<string, mixed> $orderData Данные заказа
     * @return void
     * @throws RuntimeException Если возникла ошибка при индексации
     */
    public function index(array $orderData): void
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderData['id'],
                'body' => $orderData,
            ];

            $this->client->index($params);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при индексации заказа в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Удаляет заказ из Elasticsearch
     *
     * @param OrderId $orderId Идентификатор заказа
     * @return void
     * @throws RuntimeException Если возникла ошибка при удалении
     */
    public function delete(OrderId $orderId): void
    {
        try {
            $params = [
                'index' => $this->indexName,
                'id' => $orderId->toString(),
            ];

            $this->client->delete($params);
        } catch (\Exception $e) {
            // Игнорируем ошибку, если документ не найден
            if (strpos($e->getMessage(), '404 Not Found') !== false) {
                return;
            }
            
            throw new RuntimeException(
                "Ошибка при удалении заказа из Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Создает индекс заказов в Elasticsearch
     *
     * @return void
     * @throws RuntimeException Если возникла ошибка при создании индекса
     */
    public function createIndex(): void
    {
        try {
            // Проверяем, существует ли индекс
            $indexExists = $this->client->indices()->exists(['index' => $this->indexName]);
            
            if ($indexExists) {
                return;
            }
            
            // Определяем маппинг для индекса
            $params = [
                'index' => $this->indexName,
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'customer_id' => ['type' => 'keyword'],
                            'status' => ['type' => 'keyword'],
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
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Ошибка при создании индекса заказов в Elasticsearch: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Извлекает результаты поиска из ответа Elasticsearch
     *
     * @param array<string, mixed> $response Ответ от Elasticsearch
     * @return array<array<string, mixed>> Массив документов
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
