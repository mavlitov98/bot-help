<?php

declare(strict_types=1);

namespace App\Command;

use Throwable;
use Fp\Streams\Stream;
use Psr\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Event\EventMessageHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use App\Infrastructure\RabbitMQ\ConnectionProvider;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumeEventsCommand extends Command
{
    public function __construct(
        private readonly ConnectionProvider $connectionProvider,
        private readonly EventMessageHandler $eventMessageHandler,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return 'cli:events:consume';
    }

    public function configure(): void
    {
        $this->setDescription('Чтение событий из очереди');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $connection = $this->connectionProvider->provide();
        $channel = $connection->channel();

        Stream::range(3, 11)
            ->tap(function (int $queueName) use ($channel, $connection) {
                $channel->basic_consume((string) $queueName, callback: function(AMQPMessage $msg): void {
                    try {
                        $this->eventMessageHandler->handle();
                    }
                    catch (Throwable $exception) {
                        $this->logger->error(
                            message: $exception->getMessage(),
                            context: ['exception' => $exception],
                        );
                    } finally {
                        $msg->ack();
                    }
                });

                while ($channel->is_open()) {
                    $channel->wait();
                }

                $channel->close();
                $connection->close();
            })
            ->drain();
    }
}