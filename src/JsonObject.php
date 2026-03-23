<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

use PhilipRehberger\SafeJson\Exceptions\JsonKeyException;

class JsonObject implements \JsonSerializable, \Stringable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private readonly array $data) {}

    /**
     * Get a string value by key. Supports dot notation for nested access.
     *
     * @throws JsonKeyException when key missing or value is not a string
     */
    public function string(string $key): string
    {
        $value = $this->resolve($key);

        if (! is_string($value)) {
            throw JsonKeyException::typeMismatch($key, 'string', get_debug_type($value));
        }

        return $value;
    }

    /**
     * Get an integer value by key.
     *
     * @throws JsonKeyException
     */
    public function int(string $key): int
    {
        $value = $this->resolve($key);

        if (! is_int($value)) {
            throw JsonKeyException::typeMismatch($key, 'int', get_debug_type($value));
        }

        return $value;
    }

    /**
     * Get a float value by key. Also accepts integers.
     *
     * @throws JsonKeyException
     */
    public function float(string $key): float
    {
        $value = $this->resolve($key);

        if (! is_float($value) && ! is_int($value)) {
            throw JsonKeyException::typeMismatch($key, 'float', get_debug_type($value));
        }

        return (float) $value;
    }

    /**
     * Get a boolean value by key.
     *
     * @throws JsonKeyException
     */
    public function bool(string $key): bool
    {
        $value = $this->resolve($key);

        if (! is_bool($value)) {
            throw JsonKeyException::typeMismatch($key, 'bool', get_debug_type($value));
        }

        return $value;
    }

    /**
     * Get a string value or null if missing/wrong type.
     */
    public function stringOrNull(string $key): ?string
    {
        try {
            return $this->string($key);
        } catch (JsonKeyException) {
            return null;
        }
    }

    /**
     * Get an integer value or null if missing/wrong type.
     */
    public function intOrNull(string $key): ?int
    {
        try {
            return $this->int($key);
        } catch (JsonKeyException) {
            return null;
        }
    }

    /**
     * Get a float value or null if missing/wrong type.
     */
    public function floatOrNull(string $key): ?float
    {
        try {
            return $this->float($key);
        } catch (JsonKeyException) {
            return null;
        }
    }

    /**
     * Get a boolean value or null if missing/wrong type.
     */
    public function boolOrNull(string $key): ?bool
    {
        try {
            return $this->bool($key);
        } catch (JsonKeyException) {
            return null;
        }
    }

    /**
     * Merge with another JsonObject, returning a new instance.
     * Values from the other object override on key conflict.
     */
    public function merge(self $other): self
    {
        return new self(array_merge($this->data, $other->data));
    }

    /**
     * Get an array value by key.
     *
     *
     * @return array<mixed>
     *
     * @throws JsonKeyException
     */
    public function array(string $key): array
    {
        $value = $this->resolve($key);

        if (! is_array($value)) {
            throw JsonKeyException::typeMismatch($key, 'array', get_debug_type($value));
        }

        return $value;
    }

    /**
     * Get a nested JsonObject by key.
     *
     * @throws JsonKeyException
     */
    public function object(string $key): self
    {
        $value = $this->resolve($key);

        if (! is_array($value)) {
            throw JsonKeyException::typeMismatch($key, 'object', get_debug_type($value));
        }

        return new self($value);
    }

    /**
     * Get a value by key without type enforcement.
     *
     * @throws JsonKeyException when key is missing and no default provided
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return $this->resolve($key);
        } catch (JsonKeyException) {
            if (func_num_args() >= 2) {
                return $default;
            }

            throw JsonKeyException::missing($key);
        }
    }

    /**
     * Check if a key exists (supports dot notation).
     */
    public function has(string $key): bool
    {
        try {
            $this->resolve($key);

            return true;
        } catch (JsonKeyException) {
            return false;
        }
    }

    /**
     * Return the underlying array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Return the data as a JSON string.
     */
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->data, $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Resolve a dot-notation key path.
     *
     * @throws JsonKeyException when key is missing
     */
    private function resolve(string $key): mixed
    {
        $segments = explode('.', $key);
        $current = $this->data;

        foreach ($segments as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                throw JsonKeyException::missing($key);
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
