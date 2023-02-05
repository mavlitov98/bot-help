<?php

declare(strict_types=1);

namespace App\Infrastructure\RabbitMQ;

use Fp\Streams\Stream;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Event\QueuedMessageInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Producer
{
    /**
     * @param non-empty-string $exchange
     */
    public function __construct(
        private readonly ConnectionProvider $connectionProvider,
        private readonly SerializerInterface $serializer,
        private readonly string $exchange,
    ) {
        $this->connectionProvider
            ->provide()
            ->channel()
            ->exchange_declare(exchange: $this->exchange, type: 'direct', durable: true, auto_delete: false);
    }

    /**
     * @param non-empty-string $routingKey
     * @param list<string, mixed> $headers
     */
    public function produce(QueuedMessageInterface $message, string $routingKey, array $headers = []): void
    {
        $channel = $this->connectionProvider
            ->provide()
            ->channel();

        $channel->queue_declare($message->getQueueName());
        $channel->queue_bind($message->getQueueName(), $this->exchange, $message->getRoutingKey());

        $channel->basic_publish(
            msg: $this->createAmqpMessage($message, $headers),
            exchange: $this->exchange,
            routing_key: $routingKey,
        );
    }

    public function batchProduce(QueuedMessageInterface ...$messages): void
    {
        $channel = $this->connectionProvider
            ->provide()
            ->channel();

        Stream::emits($messages)
            ->tap(function (QueuedMessageInterface $message) use ($channel): void {
                $channel->queue_declare($message->getQueueName());
                $channel->queue_bind($message->getQueueName(), $this->exchange, $message->getRoutingKey());

                $channel->batch_basic_publish(
                    message: $this->createAmqpMessage($message),
                    exchange: $this->exchange,
                    routing_key: $message->getRoutingKey(),
                );
            })
            ->drain();

        $channel->publish_batch();
    }

    /**
     * @param list<string, mixed> $headers
     */
    private function createAmqpMessage(QueuedMessageInterface $message, array $headers = []): AMQPMessage
    {
        $amqpMessage = new AMQPMessage(
            body: $this->serializer->serialize($message, 'json'),
            properties: [],
        );

        $headersTable = new AMQPTable([
            ...$headers,
            'object_type' => get_class($message),
        ]);

        $amqpMessage->set('application_headers', $headersTable);

        return $amqpMessage;
    }
}
