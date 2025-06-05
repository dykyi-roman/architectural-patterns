<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final readonly class ChangeOrderStatusResponse implements ResponderInterface
{
    public function __construct(
        private string $orderId,
        private string $status,
    ) {
    }

    public function respond(): ResponderInterface
    {
        return $this;
    }

    /**
     * @return array{order_id: string, status: string, message: string}
     */
    public function payload(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'message' => 'Order status updated successfully',
        ];
    }

    public function statusCode(): int
    {
        return 200;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
