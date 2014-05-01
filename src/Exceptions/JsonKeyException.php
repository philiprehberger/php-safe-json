<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Exceptions;

use RuntimeException;

class JsonKeyException extends RuntimeException
{
    public static function missing(string $key): self
    {
        return new self("Key '{$key}' does not exist.");
    }

    public static function typeMismatch(string $key, string $expected, string $actual): self
    {
        return new self("Key '{$key}' expected type '{$expected}', got '{$actual}'.");
    }
}
