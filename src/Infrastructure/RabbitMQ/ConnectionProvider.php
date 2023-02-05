<?php

declare(strict_types=1);

namespace App\Infrastructure\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ConnectionProvider
{
    private AMQPStreamConnection|null $connection = null;

    /**
     * @param non-empty-string $host
     * @param non-empty-string $login
     * @param non-empty-string $password
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $login,
        private readonly string $password,
    ) {}

    public function provide(): AMQPStreamConnection
    {
        if (null === $this->connection) {
            $this->connection = new AMQPStreamConnection(
                host: $this->host,
                port: $this->port,
                user: $this->login,
                password: $this->password,
            );
        }

        return $this->connection;
    }
}
