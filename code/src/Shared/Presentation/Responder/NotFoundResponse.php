<?php

declare(strict_types=1);

namespace Shared\Presentation\Responder;

final readonly class NotFoundResponse implements ResponderInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private array $payload,
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
        return $this->payload;
    }

    public function statusCode(): int
    {
        return 404;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
