<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Entity;

use InvalidArgumentException;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\ProductId;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность элемента заказа
 */
#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
final class OrderItem implements \JsonSerializable
{
    /**
     * @param ProductId $productId Идентификатор продукта
     * @param int $quantity Количество
     * @param Money $price Цена за единицу
     * @throws InvalidArgumentException Если количество меньше 1
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

        #[ORM\Column(type: 'money')]
        private readonly Money $price
    ) {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Количество не может быть меньше 1');
        }
    }

    /**
     * Создает новый элемент заказа
     *
     * @param ProductId $productId Идентификатор продукта
     * @param int $quantity Количество
     * @param Money $price Цена за единицу
     * @return self
     * @throws InvalidArgumentException Если количество меньше 1
     */
    public static function create(ProductId $productId, int $quantity, Money $price): self
    {
        return new self(null, null, $productId, $quantity, $price);
    }

    /**
     * Присваивает элемент заказа к заказу
     *
     * @param Order $order Заказ
     * @return void
     */
    public function assignToOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * Возвращает идентификатор продукта
     *
     * @return ProductId
     */
    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    /**
     * Возвращает количество
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Возвращает цену за единицу
     *
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * Возвращает общую стоимость элемента заказа
     *
     * @return Money
     */
    public function getTotalPrice(): Money
    {
        return $this->price->multiply($this->quantity);
    }

    /**
     * Сериализует элемент заказа в массив
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'product_id' => $this->productId->toString(),
            'quantity' => $this->quantity,
            'price' => [
                'amount' => $this->price->getAmount(),
                'currency' => $this->price->getCurrency(),
            ],
        ];
    }

    /**
     * Создает элемент заказа из массива
     *
     * @param array<string, mixed> $data Данные элемента заказа
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            null,
            null,
            ProductId::fromString((string) $data['product_id']),
            $data['quantity'],
            $data['money'],
        );
    }
}
