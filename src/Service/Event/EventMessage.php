<?php

declare(strict_types=1);

namespace App\Service\Event;

final class EventMessage implements QueuedMessageInterface
{
    public function __construct(
        public readonly int $eventId,
        public readonly int $accountId,
    ) {}

    public function getQueueName(): string
    {
        return (string) $this->accountId;
    }

    public function getRoutingKey(): string
    {
        return (string) $this->accountId;
    }
}