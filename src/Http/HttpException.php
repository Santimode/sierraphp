<?php
declare(strict_types=1);
namespace Sierra\Http;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode = 500,
        string $message = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int { return $this->statusCode; }
}
