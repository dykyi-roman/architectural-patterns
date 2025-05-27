<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Repository;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\ValueObject\OrderId;

interface OrderWriteModelRepositoryInterface
{
    /**
     * Сохраняет заказ
     *
     * @param Order $order Заказ для сохранения
     * @return void
     * @throws \RuntimeException При ошибке сохранения
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
