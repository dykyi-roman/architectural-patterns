<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Entity;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;
use Shared\DomainModel\Event\DomainEventInterface;

/**
 * Order Aggregate
 */
final class Order
{
    /** @var array<OrderItem> */
    private array $items = [];
    
    /** @var array<\Shared\DomainModel\Event\DomainEventInterface> */
    private array $domainEvents = [];

    /**
     * @param OrderId $id Идентификатор заказа
     * @param CustomerId $customerId Идентификатор клиента
     * @param OrderStatus $status Статус заказа
     * @param DateTimeImmutable $createdAt Дата и время создания заказа
     * @param DateTimeImmutable|null $updatedAt Дата и время последнего обновления заказа
     */
    private function __construct(
        private readonly OrderId $id,
        private readonly CustomerId $customerId,
        private OrderStatus $status,
        private readonly DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null
    ) {
    }

    public function changeStatus(OrderStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * Создает новый заказ
     *
     * @param OrderId $orderId Идентификатор заказа
     * @param CustomerId $customerId Идентификатор клиента
     * @param array<OrderItem> $items Элементы заказа
     * @return self
     * @throws InvalidArgumentException Если список элементов пуст
     */
    public static function create(OrderId $orderId, CustomerId $customerId, OrderItem ...$items): self
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Заказ должен содержать хотя бы один товар');
        }

        $now = new DateTimeImmutable();
        $order = new self(
            $orderId,
            $customerId,
            OrderStatus::CREATED,
            $now
        );

        $order->recordEvent(new OrderCreatedEvent(
            uuid_create(),
            $now,
            $orderId,
            $customerId,
            $order->calculateTotalAmount(),
            array_map(fn(OrderItem $item) => $item->jsonSerialize(), $items),
        ));

        return $order;
    }

    /**
     * Изменяет статус заказа на "Оплачен"
     *
     * @return void
     * @throws DomainException Если невозможно изменить статус
     */
    public function markAsPaid(): void
    {
        if (!$this->status->canBePaid()) {
            throw new DomainException("Невозможно изменить статус заказа {$this->id} на PAID. Текущий статус: {$this->status->value}");
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::PAID;
        $this->updatedAt = new DateTimeImmutable();

        // Генерация события изменения статуса
        $this->recordEvent(new OrderStatusChangedEvent(
            uuid_create(),
            $this->updatedAt,
            $this->id,
            $previousStatus,
            $this->status
        ));
    }

    /**
     * Изменяет статус заказа на "Отменен"
     *
     * @return void
     * @throws DomainException Если невозможно изменить статус
     */
    public function cancel(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new DomainException("Невозможно изменить статус заказа {$this->id} на CANCELLED. Текущий статус: {$this->status->value}");
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();

        // Генерация события изменения статуса
        $this->recordEvent(new OrderStatusChangedEvent(
            uuid_create(),
            $this->updatedAt,
            $this->id,
            $previousStatus,
            $this->status
        ));
    }

    /**
     * Рассчитывает общую сумму заказа
     *
     * @return Money
     * @throws InvalidArgumentException Если в заказе нет элементов или они имеют разные валюты
     */
    public function calculateTotalAmount(): Money
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Невозможно рассчитать сумму пустого заказа');
        }

        $firstItem = reset($this->items);
        $currency = $firstItem->getPrice()->getCurrency();
        $totalAmount = Money::fromAmount(0, $currency);

        foreach ($this->items as $item) {
            if ($item->getPrice()->getCurrency() !== $currency) {
                throw new InvalidArgumentException('Все элементы заказа должны быть в одной валюте');
            }
            $totalAmount = $totalAmount->add($item->getTotalPrice());
        }

        return $totalAmount;
    }

    private function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Возвращает и очищает все зарегистрированные доменные события
     *
     * @return array<DomainEventInterface>
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    /**
     * Возвращает идентификатор заказа
     *
     * @return OrderId
     */
    public function getId(): OrderId
    {
        return $this->id;
    }

    /**
     * Возвращает идентификатор клиента
     *
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * Возвращает статус заказа
     *
     * @return OrderStatus
     */
    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /**
     * Возвращает дату и время создания заказа
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Возвращает дату и время последнего обновления заказа
     *
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Возвращает элементы заказа
     *
     * @return array<OrderItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array<string, mixed> $data Данные заказа
     * @return self
     * @throws \DateMalformedStringException
     */
    public static function fromArray(array $data): self
    {
        $order = new self(
            OrderId::fromString($data['id']),
            CustomerId::fromString($data['customer_id']),
            OrderStatus::fromString($data['status']),
            new DateTimeImmutable($data['created_at']),
            isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null
        );

        foreach ($data['items'] as $itemData) {
            $order->items[] = OrderItem::fromArray($itemData);
        }

        return $order;
    }
}
