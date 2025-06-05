<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

use OrderContext\DomainModel\ValueObject\OrderId;
use Shared\Presentation\Responder\ResponderInterface;

final readonly class CreateOrderResponse implements ResponderInterface
{
    public function __construct(
        private OrderId $orderId,
    ) {
    }

    public function respond(): ResponderInterface
    {
        return $this;
    }

    /**
     * @return array{country: array{code: string}|null, city: array{name: string, transcription: string, address: string|null}|null}
     */
    public function payload(): array
    {
        return [
            'orderId' => (string) $this->orderId,
            'message' => 'Order successfully created',
        ];
    }

    public function statusCode(): int
    {
        return 201;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
