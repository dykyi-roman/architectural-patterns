<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeOrderStatusRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Order status cannot be empty')]
        #[Assert\Choice(
            choices: ['created', 'processing', 'completed', 'shipped', 'delivered', 'cancelled'],
            message: 'Invalid order status'
        )]
        public string $status,
    ) {
    }
}
