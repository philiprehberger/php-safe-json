<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Exceptions;

use RuntimeException;

class JsonDecodeException extends RuntimeException
{
    public function __construct(string $message = 'Failed to decode JSON', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
