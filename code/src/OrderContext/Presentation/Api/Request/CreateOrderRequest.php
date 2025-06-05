<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Request;

use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\ProductId;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrderRequest
{
    /**
     * @param array<array{product_id: string, quantity: int, price: int, currency: string}> $items Order items
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Customer ID cannot be empty')]
        #[Assert\Uuid(message: 'Customer ID must be a valid UUID')]
        public string $customerId,

        #[Assert\NotBlank(message: 'The order must contain at least one product')]
        #[Assert\Count(min: 1, minMessage: 'The order must contain at least one product')]
        #[Assert\All([
            new Assert\Collection([
                'product_id' => [
                    new Assert\NotBlank(message: 'Product ID cannot be empty'),
                    new Assert\Uuid(message: 'Product ID must be a valid UUID'),
                ],
                'quantity' => [
                    new Assert\NotBlank(message: 'Quantity cannot be empty'),
                    new Assert\Type(type: 'integer', message: 'Quantity must be an integer'),
                    new Assert\GreaterThan(value: 0, message: 'Quantity must be greater than 0'),
                ],
                'price' => [
                    new Assert\NotBlank(message: 'Price cannot be empty'),
                    new Assert\Type(type: 'integer', message: 'Price must be an integer'),
                    new Assert\GreaterThan(value: 0, message: 'Price must be greater than 0'),
                ],
                'currency' => [
                    new Assert\NotBlank(message: 'Currency cannot be empty'),
                    new Assert\Currency(message: 'Currency must be in ISO format 4217'),
                ],
            ]),
        ])]
        public array $items,
    ) {
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return array_map(
            fn (array $item): OrderItem => OrderItem::fromArray([
                'product_id' => ProductId::fromString((string) $item['product_id']),
                'quantity' => $item['quantity'],
                'money' => Money::fromAmount($item['price'], $item['currency']),
            ]),
            $this->items,
        );
    }
}
