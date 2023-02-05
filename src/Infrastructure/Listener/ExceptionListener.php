<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use App\Infrastructure\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

final class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        $response = $e instanceof ValidationException
            ? new JsonResponse($this->serializer->serialize($e->constraints, 'json'), json: true)
            : new JsonResponse(['exception' => $e->getMessage()]);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
