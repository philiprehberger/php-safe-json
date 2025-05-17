<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

use Generator;
use PhilipRehberger\SafeJson\Exceptions\JsonDecodeException;
use PhilipRehberger\SafeJson\Exceptions\JsonEncodeException;

class SafeJson
{
    /**
     * Decode a JSON string into a JsonObject. Throws on invalid JSON.
     */
    public static function decode(string $json): JsonObject
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonDecodeException(json_last_error_msg(), json_last_error());
        }

        if (! is_array($data)) {
            // For scalar JSON values, wrap in array
            $data = ['_value' => $data];
        }

        return new JsonObject($data);
    }

    /**
     * Decode a JSON string into a JsonObject, returning null on failure.
     */
    public static function tryDecode(string $json): ?JsonObject
    {
        try {
            return self::decode($json);
        } catch (JsonDecodeException) {
            return null;
        }
    }

    /**
     * Encode data to a JSON string. Throws on failure.
     */
    public static function encode(mixed $data, int $flags = 0): string
    {
        return json_encode($data, $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Encode data to a JSON string, returning null on failure.
     */
    public static function tryEncode(mixed $data, int $flags = 0): ?string
    {
        try {
            return self::encode($data, $flags);
        } catch (\JsonException|JsonEncodeException) {
            return null;
        }
    }

    /**
     * Compare two JSON strings and return a list of differences.
     *
     * Each difference includes an operation ('add', 'remove', 'replace'),
     * a path, and the relevant values.
     *
     * @return array<array{op: string, path: string, value?: mixed, old?: mixed}>
     *
     * @throws JsonDecodeException when either JSON string is invalid
     */
    public static function diff(string $jsonA, string $jsonB): array
    {
        $a = self::decode($jsonA)->toArray();
        $b = self::decode($jsonB)->toArray();

        return JsonDiff::diff($a, $b);
    }

    /**
     * Stream-decode a JSON file containing a top-level array, yielding one element at a time.
     *
     * Memory efficient for large files since only one element is held in memory at a time.
     *
     * @return Generator<int, mixed>
     *
     * @throws JsonDecodeException when the file cannot be opened or contains invalid JSON
     */
    public static function decodeStream(string $filePath): Generator
    {
        return StreamDecoder::decodeStream($filePath);
    }
}
