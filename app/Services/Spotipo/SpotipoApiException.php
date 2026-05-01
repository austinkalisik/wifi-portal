<?php

namespace App\Services\Spotipo;

use RuntimeException;

class SpotipoApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly array $context = [],
    ) {
        parent::__construct($message, $status);
    }
}
