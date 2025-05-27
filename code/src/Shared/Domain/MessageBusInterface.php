<?php

declare(strict_types=1);

namespace Shared\Domain;

interface MessageBusInterface
{
    /**
     * @throws \Throwable
     */
    public function dispatch(object $message): mixed;
}