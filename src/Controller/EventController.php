<?php

declare(strict_types=1);

namespace App\Controller;

use Fp\Collections\ArrayList;
use App\Service\Event\EventMessage;
use App\Infrastructure\RabbitMQ\Producer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly Producer $producer,
    ) {}

    #[Route(path: '/event', methods: ['POST'])]
    public function event(): JsonResponse
    {
        $messages = ArrayList::collect(range(1, 1000))
            ->flatMap(fn(int $accountId) => ArrayList::collect(range(1, 10))
                ->map(fn(int $eventId) => new EventMessage($eventId, $accountId))
                ->toList());

        $this->producer->batchProduce(...$messages);

        return $this->json([
            'message' => 'Events successfully added to queue',
        ]);
    }
}
