<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

interface DomainEventInterface extends \JsonSerializable
{
    public function getEventId(): string;
    
    public function getOccurredOn(): \DateTimeImmutable;
    
    public function getEventName(): string;
    
    public function getAggregateId(): string;
}
