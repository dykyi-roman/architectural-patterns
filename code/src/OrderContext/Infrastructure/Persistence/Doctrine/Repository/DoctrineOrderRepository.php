<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use OrderContext\DomainModel\ValueObject\OrderId;
use RuntimeException;

/**
 * Реализация репозитория для работы с заказами через Doctrine ORM
 */
final readonly class DoctrineOrderRepository implements OrderWriteModelRepositoryInterface
{
    /**
     * @param EntityManagerInterface $entityManager Менеджер сущностей Doctrine
     */
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(Order $order): void
    {
        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при сохранении заказа: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(OrderId $orderId): bool
    {
        try {
            $repository = $this->entityManager->getRepository(Order::class);
            $count = $repository->count(['id' => $orderId->toString()]);
            
            return $count > 0;
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при проверке существования заказа: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @throw OrderNotFoundException
     */
    public function findById(OrderId $orderId): Order
    {
        $repository = $this->entityManager->getRepository(Order::class);
        $order = $repository->find($orderId->toString());

        return $order ?? throw new OrderNotFoundException($orderId);
    }
}
