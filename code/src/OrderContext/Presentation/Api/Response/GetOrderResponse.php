<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final readonly class GetOrderResponse implements ResponderInterface
{
    public function __construct(
        private OrderResponse $order,
    ) {
    }

    public function respond(): ResponderInterface
    {
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->order->jsonSerialize();
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
