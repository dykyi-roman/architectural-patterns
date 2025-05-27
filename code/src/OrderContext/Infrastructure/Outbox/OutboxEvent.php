<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use DateTimeImmutable;

final class OutboxEvent
{
    /**
     * @param string $id Уникальный идентификатор записи
     * @param string $eventId Идентификатор события
     * @param string $eventType Тип события
     * @param string $aggregateId Идентификатор агрегата
     * @param string $payload Сериализованные данные события
     * @param DateTimeImmutable $createdAt Дата и время создания записи
     * @param DateTimeImmutable|null $processedAt Дата и время обработки записи
     * @param bool $isProcessed Статус обработки
     * @param int $retryCount Количество попыток обработки
     * @param string|null $error Описание ошибки при последней попытке обработки
     */
    public function __construct(
        private readonly string $id,
        private readonly string $eventId,
        private readonly string $eventType,
        private readonly string $aggregateId,
        private readonly string $payload,
        private readonly DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $processedAt = null,
        private bool $isProcessed = false,
        private int $retryCount = 0,
        private ?string $error = null
    ) {
    }

    /**
     * Создает новую запись о событии для outbox
     *
     * @param string $eventId Идентификатор события
     * @param string $eventType Тип события
     * @param string $aggregateId Идентификатор агрегата
     * @param string $payload Сериализованные данные события
     * @return self
     */
    public static function create(string $eventId, string $eventType, string $aggregateId, string $payload): self
    {
        return new self(
            uuid_create(),
            $eventId,
            $eventType,
            $aggregateId,
            $payload,
            new DateTimeImmutable()
        );
    }

    /**
     * Помечает событие как обработанное
     *
     * @return void
     */
    public function markAsProcessed(): void
    {
        $this->isProcessed = true;
        $this->processedAt = new DateTimeImmutable();
    }

    /**
     * Увеличивает счетчик попыток обработки и устанавливает описание ошибки
     *
     * @param string $error Описание ошибки
     * @return void
     */
    public function increaseRetryCount(string $error): void
    {
        $this->retryCount++;
        $this->error = $error;
    }

    /**
     * Возвращает идентификатор записи
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Возвращает идентификатор события
     *
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * Возвращает тип события
     *
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * Возвращает идентификатор агрегата
     *
     * @return string
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * Возвращает сериализованные данные события
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Возвращает дату и время создания записи
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Возвращает дату и время обработки записи
     *
     * @return DateTimeImmutable|null
     */
    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    /**
     * Возвращает статус обработки
     *
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    /**
     * Возвращает количество попыток обработки
     *
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * Возвращает описание ошибки при последней попытке обработки
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
