<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

use Generator;
use PhilipRehberger\SafeJson\Exceptions\JsonDecodeException;

class StreamDecoder
{
    /**
     * Stream-decode a JSON file containing a top-level array, yielding one element at a time.
     *
     * This is memory efficient for large files since only one element is held in memory at a time.
     * The file must contain a JSON array at the top level (e.g., `[{...}, {...}, ...]`).
     *
     * @return Generator<int, mixed>
     *
     * @throws JsonDecodeException when the file cannot be opened or contains invalid JSON
     */
    public static function decodeStream(string $filePath): Generator
    {
        if (! file_exists($filePath)) {
            throw new JsonDecodeException("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new JsonDecodeException("Unable to open file: {$filePath}");
        }

        try {
            yield from self::parseArray($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * Parse a top-level JSON array from a file handle, yielding each element.
     *
     * @param  resource  $handle
     * @return Generator<int, mixed>
     */
    private static function parseArray($handle): Generator
    {
        $depth = 0;
        $inString = false;
        $escape = false;
        $buffer = '';
        $started = false;
        $index = 0;

        while (! feof($handle)) {
            $chunk = fread($handle, 8192);

            if ($chunk === false) {
                break;
            }

            $len = strlen($chunk);

            for ($i = 0; $i < $len; $i++) {
                $char = $chunk[$i];

                if ($escape) {
                    $buffer .= $char;
                    $escape = false;

                    continue;
                }

                if ($inString) {
                    $buffer .= $char;

                    if ($char === '\\') {
                        $escape = true;
                    } elseif ($char === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($char === '"') {
                    $inString = true;
                    $buffer .= $char;

                    continue;
                }

                if ($char === '[' && ! $started && $depth === 0) {
                    $started = true;

                    continue;
                }

                if (! $started) {
                    // Skip whitespace before the opening bracket
                    if (trim($char) === '') {
                        continue;
                    }

                    throw new JsonDecodeException('Expected top-level JSON array');
                }

                if ($char === '{' || $char === '[') {
                    $depth++;
                    $buffer .= $char;

                    continue;
                }

                if ($char === '}' || $char === ']') {
                    if ($depth === 0) {
                        // Closing bracket of the top-level array
                        $trimmed = trim($buffer);

                        if ($trimmed !== '') {
                            $decoded = json_decode($trimmed, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new JsonDecodeException(json_last_error_msg(), json_last_error());
                            }

                            yield $index => $decoded;
                        }

                        return;
                    }

                    $depth--;
                    $buffer .= $char;

                    continue;
                }

                if ($char === ',' && $depth === 0) {
                    $trimmed = trim($buffer);

                    if ($trimmed !== '') {
                        $decoded = json_decode($trimmed, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new JsonDecodeException(json_last_error_msg(), json_last_error());
                        }

                        yield $index => $decoded;
                        $index++;
                    }

                    $buffer = '';

                    continue;
                }

                $buffer .= $char;
            }
        }
    }
}
