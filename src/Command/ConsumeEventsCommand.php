<?php

declare(strict_types=1);

namespace App\Command;

use Amp\Loop;
use PHPinnacle\Ridge\Channel;
use PHPinnacle\Ridge\Client;
use PHPinnacle\Ridge\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Amp\delay;
use function Amp\asyncCall;

final class ConsumeEventsCommand extends Command
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $login,
        private readonly string $password,
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

    private static function handleMessage(Message $message, Channel $channel): void
    {
        asyncCall(function () use ($message, $channel) {
            // Simulate useful IO work.
            // HTTP request for example.
            yield delay(1);

            yield $channel->ack($message);
        });
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        Loop::run(function () use ($output) {
            $client = Client::create("amqp://{$this->login}:{$this->password}@{$this->host}:{$this->port}");
            yield $client->connect();

            $output->writeln('Declare consumers...');

            $channel = yield $client->channel();

            foreach (range(1, 1000) as $queueName) {
                yield $channel->consume(
                    callback: self::handleMessage(...),
                    queue: (string) $queueName,
                );
            }
            $output->writeln('Consumers declared!');

            // Graceful shutdown handler.
            $terminate = function (string $id) use ($channel, $client, $output) {
                Loop::cancel($id);

                $output->writeln('Disconnecting...');
                yield $channel->close();
                yield $client->disconnect();
                $output->writeln('Disconnected!');

                Loop::stop();
            };

            Loop::onSignal(SIGINT, $terminate);
            Loop::onSignal(SIGTERM, $terminate);
        });

        return self::SUCCESS;
    }
}