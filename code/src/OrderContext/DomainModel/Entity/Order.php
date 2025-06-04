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
use Shared\DomainModel\Entity\AbstractAggregateRoot;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Order Aggregate
 */
#[ORM\Entity]
#[ORM\Table(name: 'orders')]
final class Order extends AbstractAggregateRoot
{
    /** 
     * @var Collection<int, OrderItem> 
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'order_id')]
        private readonly OrderId $id,
        
        #[ORM\Column(type: 'customer_id')]
        private readonly CustomerId $customerId,
        
        #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
        private OrderStatus $status,
        
        #[ORM\Column(type: 'datetime_immutable')]
        private readonly DateTimeImmutable $createdAt,
        
        #[ORM\Column(name: 'total_amount', type: 'decimal', precision: 10, scale: 2)]
        private float $totalAmountValue,
        
        #[ORM\Column(name: 'currency', type: 'string', length: 3)]
        private string $currency,
        
        #[ORM\Column(type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $updatedAt = null,
        
        #[ORM\Column(name: 'version', type: 'integer')]
        private int $version = 1
    ) {
        $this->items = new ArrayCollection();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeStatus(OrderStatus $status): void
    {
        $previousStatus = $this->status;
        $this->status = $status;

        $this->recordEvent(
            new OrderStatusChangedEvent(
                Uuid::v4()->toRfc4122(),
                new \DateTimeImmutable(),
                $this->id,
                $previousStatus,
                $this->status,
            )
        );
    }

    /**
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
            $now,
            0.0,
            'USD'
        );

        foreach ($items as $item) {
            $order->addItem($item);
        }
        
        $totalAmount = $order->calculateTotalAmount();
        $order->updateTotalAmount($totalAmount);

        $order->recordEvent(
            new OrderCreatedEvent(
                uuid_create(),
                $now,
                $orderId,
                $customerId,
                $totalAmount,
                array_map(fn(OrderItem $item) => $item->jsonSerialize(), $items),
            )
        );

        return $order;
    }

    /**
     * Воссоздает объект Order из хранилища данных
     *
     * @param OrderId $id Идентификатор заказа
     * @param CustomerId $customerId Идентификатор клиента
     * @param OrderStatus $status Статус заказа
     * @param DateTimeImmutable $createdAt Дата и время создания заказа
     * @param float $totalAmountValue Значение общей суммы заказа
     * @param string $currency Валюта заказа
     * @param DateTimeImmutable|null $updatedAt Дата и время последнего обновления заказа
     * @param int $version Версия заказа
     * @param OrderItem ...$items Элементы заказа
     * @return self
     */
    public static function reconstruct(
        OrderId $id,
        CustomerId $customerId,
        OrderStatus $status,
        DateTimeImmutable $createdAt,
        float $totalAmountValue,
        string $currency,
        ?DateTimeImmutable $updatedAt = null,
        int $version = 1,
        OrderItem ...$items
    ): self {
        $order = new self(
            $id,
            $customerId,
            $status,
            $createdAt,
            $totalAmountValue,
            $currency,
            $updatedAt,
            $version
        );

        foreach ($items as $item) {
            $order->addItem($item);
        }

        return $order;
    }

    /**
     * Добавляет элемент заказа
     *
     * @param OrderItem $item Элемент заказа
     * @return void
     */
    public function addItem(OrderItem $item): void
    {
        $item->assignToOrder($this);
        $this->items->add($item);
        
        // Обновляем общую сумму заказа при добавлении нового элемента
        if ($this->items->count() > 0) {
            $totalAmount = $this->calculateTotalAmount();
            $this->updateTotalAmount($totalAmount);
        }
    }

    private function updateTotalAmount(Money $totalAmount): void
    {
        $this->totalAmountValue = $totalAmount->getAmount();
        $this->currency = $totalAmount->getCurrency();
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
            throw new DomainException(
                "Невозможно изменить статус заказа {$this->id} на PAID. Текущий статус: {$this->status->value}"
            );
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::PAID;
        $this->updatedAt = new DateTimeImmutable();

        // Генерация события изменения статуса
        $this->recordEvent(
            new OrderStatusChangedEvent(
                uuid_create(),
                $this->updatedAt,
                $this->id,
                $previousStatus,
                $this->status
            )
        );
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
            throw new DomainException(
                "Невозможно изменить статус заказа {$this->id} на CANCELLED. Текущий статус: {$this->status->value}"
            );
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();

        // Генерация события изменения статуса
        $this->recordEvent(
            new OrderStatusChangedEvent(
                uuid_create(),
                $this->updatedAt,
                $this->id,
                $previousStatus,
                $this->status
            )
        );
    }

    /**
     * Рассчитывает общую сумму заказа
     *
     * @return Money
     * @throws InvalidArgumentException Если в заказе нет элементов или они имеют разные валюты
     */
    public function calculateTotalAmount(): Money
    {
        if ($this->items->isEmpty()) {
            throw new InvalidArgumentException('Невозможно рассчитать сумму пустого заказа');
        }

        $firstItem = $this->items->first();
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getId(): OrderId
    {
        return $this->id;
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /**
     * Возвращает дату и время создания заказа
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Возвращает дату и время последнего обновления заказа
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Возвращает элементы заказа
     *
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
