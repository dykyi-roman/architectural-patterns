<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for order status change request
 */
final readonly class ChangeOrderStatusRequest
{
    /**
     * @param string $status New order status
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Статус заказа не может быть пустым')]
        #[Assert\Choice(
            choices: ['created', 'processing', 'completed', 'shipped', 'delivered', 'cancelled'],
            message: 'Недопустимый статус заказа'
        )]
        public string $status
    ) {
    }
}
