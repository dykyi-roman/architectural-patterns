<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrderHistory\Query;

use OrderContext\Application\UseCases\GetOrderHistory\Dto\EventDto;
use OrderContext\Application\UseCases\GetOrderHistory\Dto\OrderHistoryDto;
use OrderContext\Application\UseCases\GetOrderHistory\Exception\HistoryNotFoundException;
use Shared\Application\Exception\ApplicationException;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\DomainModel\Service\EventStoreInterface;

final readonly class GetOrderHistoryQueryHandler
{
    public function __construct(
        private EventStoreInterface $eventStore,
    ) {
    }

    /**
     * @throws ApplicationException
     */
    public function __invoke(GetOrderHistoryQuery $query): OrderHistoryDto
    {
        $events = $this->eventStore->getEventsForAggregate($query->orderId);
        if ([] === $events) {
            throw new HistoryNotFoundException($query->orderId);
        }

        return new OrderHistoryDto(
            $query->orderId,
            array_map(
                static fn (DomainEventInterface $event) => new EventDto(
                    $event->getEventId(),
                    $event->getOccurredAt(),
                    $event->getEventName(),
                    $event->jsonSerialize(),
                ),
                $events,
            )
        );
    }
}
