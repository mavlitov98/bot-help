<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends Exception
{
    public function __construct(
        public readonly ConstraintViolationListInterface $constraints,
    ) {
        parent::__construct();
    }
}
