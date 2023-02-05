<?php

declare(strict_types=1);

namespace App\Service\Event;

interface QueuedMessageInterface
{
    public function getQueueName(): string;

    public function getRoutingKey(): string;
}
