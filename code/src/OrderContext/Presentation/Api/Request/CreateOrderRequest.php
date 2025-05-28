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
        #[Assert\NotBlank(message: 'Идентификатор клиента не может быть пустым')]
        #[Assert\Uuid(message: 'Идентификатор клиента должен быть валидным UUID')]
        public string $customerId,

        #[Assert\NotBlank(message: 'Заказ должен содержать хотя бы один товар')]
        #[Assert\Count(min: 1, minMessage: 'Заказ должен содержать хотя бы один товар')]
        #[Assert\All([
            new Assert\Collection([
                'product_id' => [
                    new Assert\NotBlank(message: 'Идентификатор продукта не может быть пустым'),
                    new Assert\Uuid(message: 'Идентификатор продукта должен быть валидным UUID'),
                ],
                'quantity' => [
                    new Assert\NotBlank(message: 'Количество не может быть пустым'),
                    new Assert\Type(type: 'integer', message: 'Количество должно быть целым числом'),
                    new Assert\GreaterThan(value: 0, message: 'Количество должно быть больше 0'),
                ],
                'price' => [
                    new Assert\NotBlank(message: 'Цена не может быть пустой'),
                    new Assert\Type(type: 'integer', message: 'Цена должна быть целым числом'),
                    new Assert\GreaterThan(value: 0, message: 'Цена должна быть больше 0'),
                ],
                'currency' => [
                    new Assert\NotBlank(message: 'Валюта не может быть пустой'),
                    new Assert\Currency(message: 'Валюта должна быть в формате ISO 4217'),
                ],
            ]),
        ])]
        public array $items
    ) {
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return array_map(
            fn(array $item): OrderItem => OrderItem::fromArray([
                'product_id' => ProductId::fromString($item['product_id']),
                'quantity' => $item['quantity'],
                'price' => Money::fromAmount($item['price'], $item['currency']),
            ]),
            $this->items,
        );
    }
}