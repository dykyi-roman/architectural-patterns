<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Repository;

use OrderContext\DomainModel\Enum\OrderStatus;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;

interface OrderReadModelRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     *
     * @throws \RuntimeException
     */
    public function findById(OrderId $orderId): ?array;

    /**
     * @return array<array<string, mixed>>
     *
     * @throws \RuntimeException
     */
    public function findByCustomerId(CustomerId $customerId, int $offset = 0, int $limit = 20): array;

    /**
     * @return array<array<string, mixed>>
     *
     * @throws \RuntimeException
     */
    public function findByStatus(OrderStatus $status, int $offset = 0, int $limit = 20): array;

    /**
     * @throws \RuntimeException
     */
    public function count(): int;

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{items: array<array<string, mixed>>, total: int}
     *
     * @throws \RuntimeException
     */
    public function findAll(
        array $filters = [],
        int $page = 1,
        int $limit = 20,
        ?string $sortBy = null,
        string $sortDirection = 'desc',
    ): array;
}
