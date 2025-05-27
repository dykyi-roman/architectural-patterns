<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Repository;

use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;

interface OrderReadModelRepositoryInterface
{
    /**
     * @return array<string, mixed>|null Данные заказа или null, если не найден
     * @throws \RuntimeException При ошибке чтения из хранилища
     */
    public function findById(OrderId $orderId): ?array;

    /**
     * Находит все заказы клиента
     *
     * @param CustomerId $customerId Идентификатор клиента
     * @param int $offset Смещение для пагинации
     * @param int $limit Ограничение количества результатов
     * @return array<array<string, mixed>> Массив данных заказов
     * @throws \RuntimeException При ошибке чтения из хранилища
     */
    public function findByCustomerId(CustomerId $customerId, int $offset = 0, int $limit = 20): array;

    /**
     * Находит заказы по статусу
     *
     * @param OrderStatus $status Статус заказа
     * @param int $offset Смещение для пагинации
     * @param int $limit Ограничение количества результатов
     * @return array<array<string, mixed>> Массив данных заказов
     * @throws \RuntimeException При ошибке чтения из хранилища
     */
    public function findByStatus(OrderStatus $status, int $offset = 0, int $limit = 20): array;
    
    /**
     * Возвращает общее количество заказов
     *
     * @return int Количество заказов
     * @throws \RuntimeException При ошибке чтения из хранилища
     */
    public function count(): int;
    
    /**
     * Находит все заказы с пагинацией
     *
     * @param int $offset Смещение для пагинации
     * @param int $limit Ограничение количества результатов
     * @return array<array<string, mixed>> Массив данных заказов
     * @throws \RuntimeException При ошибке чтения из хранилища
     */
    public function findAll(int $offset = 0, int $limit = 20): array;
}
