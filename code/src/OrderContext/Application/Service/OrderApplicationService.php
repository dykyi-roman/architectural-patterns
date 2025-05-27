<?php

declare(strict_types=1);

namespace OrderContext\Application\Service;

use Psr\Log\LoggerInterface;
use Shared\Domain\MessageBusInterface;
use Throwable;

final readonly class OrderApplicationService
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute(object $command): void
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }

    /**
     * @throws \Throwable
     */
    public function query(object $query): mixed
    {
        try {
            return $this->queryBus->dispatch($query);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }
}
