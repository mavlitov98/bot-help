<?php

declare(strict_types=1);

namespace App\Infrastructure\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ConnectionProvider
{
    /**
     * @param non-empty-string $host
     * @param non-empty-string $port
     * @param non-empty-string $login
     * @param non-empty-string $password
     */
    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $login,
        private readonly string $password,
    ) {}

    public function provide(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            host: $this->host,
            port: (int) $this->port,
            user: $this->login,
            password: $this->password,
        );
    }
}
