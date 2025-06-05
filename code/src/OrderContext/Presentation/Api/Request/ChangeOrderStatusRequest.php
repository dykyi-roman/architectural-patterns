<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Request;

use OrderContext\DomainModel\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final readonly class ChangeOrderStatusRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Order status cannot be empty')]
        #[Assert\Callback(callback: [self::class, 'validateStatus'])]
        public string $status,
    ) {
    }

    public static function validateStatus(string $status, ExecutionContextInterface $context): void
    {
        if (!in_array($status, array_column(OrderStatus::cases(), 'value'), true)) {
            $context->buildViolation('Invalid order status')
                ->atPath('status')
                ->addViolation();
        }
    }
}
