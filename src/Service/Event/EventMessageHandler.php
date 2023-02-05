<?php

declare(strict_types=1);

namespace App\Service\Event;

final class EventMessageHandler
{
    public function handle(): void
    {
        sleep(1);
    }
}
