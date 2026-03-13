<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

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
}
