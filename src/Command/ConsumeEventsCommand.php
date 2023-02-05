<?php

declare(strict_types=1);

namespace App\Command;

use Amp\Loop;
use Throwable;
use Psr\Log\LoggerInterface;
use PHPinnacle\Ridge\Client;
use Fp\Collections\ArrayList;
use PHPinnacle\Ridge\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Amp\delay;
use function Amp\Promise\all;

final class ConsumeEventsCommand extends Command
{
    public function __construct(
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
        Loop::run(function () {
            $client = Client::create('amqp://root:root@bot-help-rabbitmq:5672');
            yield $client->connect();

            $channel = yield $client->channel();

            $promises = ArrayList::range(1, 1001)
                ->map(fn(int $queueName) => $channel->consume(
                    callback: function (Message $message) use ($channel) {
                        try {
                            yield delay(1);
                        } catch (Throwable $exception) {
                            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
                        } finally {
                            yield $channel->ack($message);
                        }
                    },
                    queue: (string) $queueName,
                ))
                ->toList();

            yield all($promises);
        });
    }
}