<?php

declare(strict_types=1);

namespace OrderContext\Application\Service;

use Psr\Log\LoggerInterface;
use Shared\Application\Exception\ApplicationException;
use Shared\DomainModel\Enum\GeneralErrorCode;
use Shared\DomainModel\Exception\DomainException;
use Shared\DomainModel\Service\MessageBusInterface;
use Throwable;

final readonly class OrderApplicationService
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private LoggerInterface $logger,
    ) {
    }

    public function command(object $command): void
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (DomainException $exception) {
            $this->logger->error($exception->getMessage(), $exception->jsonSerialize());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @throws \Shared\Application\Exception\ApplicationException
     */
    public function query(object $query): mixed
    {
        try {
            return $this->queryBus->dispatch($query);
        } catch (DomainException $exception) {
            throw new ApplicationException(
                get_class($query),
                $exception->getErrorCode(),
                'Query execution failed',
                $exception->context,
                $exception,
            );
        } catch (Throwable $exception) {
            throw new ApplicationException(
                get_class($query),
                GeneralErrorCode::UNEXPECTED_ERROR,
                'Query execution failed',
                [],
                $exception,
            );
        }
    }
}
