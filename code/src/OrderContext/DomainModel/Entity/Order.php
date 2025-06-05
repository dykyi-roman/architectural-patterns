<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;
use Shared\DomainModel\Entity\AbstractAggregateRoot;
use Symfony\Component\Uid\Uuid;

/**
 * Order Aggregate.
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
        private readonly \DateTimeImmutable $createdAt,

        #[ORM\Column(name: 'total_amount', type: 'decimal', precision: 10, scale: 2)]
        private float $totalAmountValue,

        #[ORM\Column(name: 'currency', type: 'string', length: 3)]
        private string $currency,

        #[ORM\Column(type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,

        #[ORM\Column(name: 'version', type: 'integer')]
        private int $version = 1,
    ) {
        $this->items = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
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
     * @param array<OrderItem> $items
     *
     * @throws \InvalidArgumentException
     */
    public static function create(OrderId $orderId, CustomerId $customerId, OrderItem ...$items): self
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('The order must contain at least one product');
        }

        $now = new \DateTimeImmutable();
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
                array_map(fn (OrderItem $item) => $item->jsonSerialize(), $items),
            )
        );

        return $order;
    }

    public function addItem(OrderItem $item): void
    {
        $item->assignToOrder($this);
        $this->items->add($item);

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

    public function markAsPaid(): void
    {
        if (!$this->status->canBePaid()) {
            throw new \DomainException("Unable to change order status {$this->id} to PAID. Current status: {$this->status->value}");
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::PAID;
        $this->updatedAt = new \DateTimeImmutable();

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

    public function cancel(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \DomainException("Unable to change order status {$this->id} to CANCELLED. Current status: {$this->status->value}");
        }

        $previousStatus = $this->status;
        $this->status = OrderStatus::CANCELLED;
        $this->updatedAt = new \DateTimeImmutable();

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
     * @throws \InvalidArgumentException
     */
    public function calculateTotalAmount(): Money
    {
        if ($this->items->isEmpty()) {
            throw new \InvalidArgumentException('Unable to calculate the amount of an empty order');
        }

        $firstItem = $this->items->first();
        $currency = $firstItem->getPrice()->getCurrency();
        $totalAmount = Money::fromAmount(0, $currency);

        foreach ($this->items as $item) {
            if ($item->getPrice()->getCurrency() !== $currency) {
                throw new \InvalidArgumentException('All order elements must be in the same currency.');
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
