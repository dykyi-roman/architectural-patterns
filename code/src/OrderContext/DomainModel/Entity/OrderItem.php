<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Entity;

use Doctrine\ORM\Mapping as ORM;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\ProductId;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
final class OrderItem implements \JsonSerializable
{
    #[ORM\Transient]
    private Money $price;

    /**
     * @throws \InvalidArgumentException
     */
    private function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'AUTO')]
        #[ORM\Column(type: 'integer')]
        private ?int $id = null,

        #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
        #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
        private ?Order $order = null,

        #[ORM\Column(type: 'product_id')]
        private readonly ProductId $productId,

        #[ORM\Column(type: 'integer')]
        private readonly int $quantity,

        #[ORM\Column(name: 'price', type: 'decimal', precision: 10, scale: 2)]
        private readonly float $priceAmount,

        #[ORM\Column(name: 'currency', type: 'string', length: 3)]
        private readonly string $currency,
    ) {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('The quantity cannot be less than 1');
        }

        $this->price = Money::fromAmount($priceAmount, $currency);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function create(ProductId $productId, int $quantity, Money $price): self
    {
        return new self(
            null,
            null,
            $productId,
            $quantity,
            $price->getAmount(),
            $price->getCurrency()
        );
    }

    public function assignToOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getTotalPrice(): Money
    {
        return $this->price->multiply($this->quantity);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'product_id' => $this->productId->toString(),
            'quantity' => $this->quantity,
            'price' => [
                'amount' => $this->priceAmount,
                'currency' => $this->currency,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $price = $data['price'] ?? [];
        $amount = $price['amount'] ?? 0;
        $currency = $price['currency'] ?? 'USD';

        return new self(
            null,
            null,
            ProductId::fromString((string) $data['product_id']),
            (int) $data['quantity'],
            (float) $amount,
            $currency
        );
    }
}
