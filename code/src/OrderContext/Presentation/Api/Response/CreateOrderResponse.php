<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final readonly class CreateOrderResponse implements ResponderInterface
{
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
            'message' => 'Order successfully created',
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
