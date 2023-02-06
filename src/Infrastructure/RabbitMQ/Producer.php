<?php

declare(strict_types=1);

namespace App\Infrastructure\RabbitMQ;

use Fp\Streams\Stream;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Event\QueuedMessageInterface;

final class Producer
{
    /**
     * @param non-empty-string $exchange
     */
    public function __construct(
        private readonly ConnectionProvider $connectionProvider,
        private readonly string $exchange,
    ) {}

    public function batchProduce(QueuedMessageInterface ...$messages): void
    {
        $channel = $this->connectionProvider
            ->provide()
            ->channel();

        $channel->exchange_declare(
            exchange: $this->exchange,
            type: 'direct',
            durable: true,
            auto_delete: false,
        );

        Stream::emits($messages)
            ->tap(function (QueuedMessageInterface $message) use ($channel): void {
                $args = new AMQPTable([
                    // https://www.rabbitmq.com/consumers.html#single-active-consumer
                    // Для гарантии обработки сообщений по порядку, в рамках одной очереди.
                    'x-single-active-consumer' => true,
                ]);

                $channel->queue_declare($message->getQueueName(), durable: true, auto_delete: false, arguments: $args);
                $channel->queue_bind($message->getQueueName(), $this->exchange, $message->getRoutingKey());

                $channel->batch_basic_publish(
                    message: new AMQPMessage(json_encode($message)),
                    exchange: $this->exchange,
                    routing_key: $message->getRoutingKey(),
                );
            })
            ->drain();

        $channel->publish_batch();
    }
}
