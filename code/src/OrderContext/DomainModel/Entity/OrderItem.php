<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Entity;

use InvalidArgumentException;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\ProductId;

/**
 * Сущность элемента заказа
 */
final readonly class OrderItem
{
    /**
     * @param ProductId $productId Идентификатор продукта
     * @param int $quantity Количество
     * @param Money $price Цена за единицу
     * @throws InvalidArgumentException Если количество меньше 1
     */
    private function __construct(
        private ProductId $productId,
        private int $quantity,
        private Money $price
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
        return new self($productId, $quantity, $price);
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
    public function toArray(): array
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
            ProductId::fromString($data['product_id']),
            $data['quantity'],
            Money::fromAmount($data['price']['amount'], $data['price']['currency'])
        );
    }
}
