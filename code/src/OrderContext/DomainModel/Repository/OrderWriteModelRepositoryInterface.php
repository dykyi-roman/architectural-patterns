<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Repository;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\Exception\SaveOrderException;
use OrderContext\DomainModel\ValueObject\OrderId;

interface OrderWriteModelRepositoryInterface
{
    /**
     * @throws SaveOrderException
     */
    public function save(Order $order): void;

    /**
     * Проверяет существование заказа по идентификатору
     *
     * @param OrderId $orderId Идентификатор заказа
     * @return bool Существует ли заказ
     * @throws \RuntimeException При ошибке проверки
     */
    public function exists(OrderId $orderId): bool;

    /**
     * @throw OrderNotFoundException
     */
    public function findById(OrderId $orderId): Order;
}
