<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

class JsonPath
{
    /**
     * Query data using a JSON Path expression.
     *
     * Supported syntax:
     * - `$` root
     * - `.key` or `['key']` child access
     * - `[0]` array index
     * - `[*]` wildcard (all elements)
     * - `..key` recursive descent
     * - `[0:3]` array slice
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public static function query(array $data, string $path): array
    {
        $tokens = self::tokenize($path);

        return self::resolve([$data], $tokens);
    }

    /**
     * Tokenize a JSON Path expression into traversal tokens.
     *
     * @return array<array{type: string, value: string|int|null, end?: int}>
     */
    private static function tokenize(string $path): array
    {
        $tokens = [];
        $length = strlen($path);
        $i = 0;

        if ($i < $length && $path[$i] === '$') {
            $i++;
        }

        while ($i < $length) {
            if ($path[$i] === '.') {
                $i++;

                if ($i < $length && $path[$i] === '.') {
                    // Recursive descent
                    $i++;
                    $key = self::readKey($path, $i, $length);
                    $tokens[] = ['type' => 'recursive', 'value' => $key];
                } else {
                    $key = self::readKey($path, $i, $length);
                    $tokens[] = ['type' => 'child', 'value' => $key];
                }
            } elseif ($path[$i] === '[') {
                $i++;
                $content = '';

                while ($i < $length && $path[$i] !== ']') {
                    $content .= $path[$i];
                    $i++;
                }

                if ($i < $length) {
                    $i++; // skip ]
                }

                $content = trim($content);

                if ($content === '*') {
                    $tokens[] = ['type' => 'wildcard', 'value' => null];
                } elseif (str_contains($content, ':')) {
                    $parts = explode(':', $content);
                    $start = $parts[0] !== '' ? (int) $parts[0] : 0;
                    $end = isset($parts[1]) && $parts[1] !== '' ? (int) $parts[1] : null;
                    $tokens[] = ['type' => 'slice', 'value' => [$start, $end]];
                } elseif ($content[0] === "'" || $content[0] === '"') {
                    $key = trim($content, "\"'");
                    $tokens[] = ['type' => 'child', 'value' => $key];
                } else {
                    $tokens[] = ['type' => 'index', 'value' => (int) $content];
                }
            } else {
                $key = self::readKey($path, $i, $length);
                $tokens[] = ['type' => 'child', 'value' => $key];
            }
        }

        return $tokens;
    }

    /**
     * Read a key name from the path string, advancing the position.
     */
    private static function readKey(string $path, int &$i, int $length): string
    {
        $key = '';

        while ($i < $length && $path[$i] !== '.' && $path[$i] !== '[') {
            $key .= $path[$i];
            $i++;
        }

        return $key;
    }

    /**
     * Resolve tokens against a set of current values.
     *
     * @param  array<mixed>  $current
     * @param  array<array{type: string, value: mixed}>  $tokens
     * @return array<mixed>
     */
    private static function resolve(array $current, array $tokens): array
    {
        if ($tokens === []) {
            return $current;
        }

        $token = array_shift($tokens);
        $next = [];

        foreach ($current as $value) {
            switch ($token['type']) {
                case 'child':
                    if (is_array($value) && array_key_exists($token['value'], $value)) {
                        $next[] = $value[$token['value']];
                    }
                    break;

                case 'index':
                    if (is_array($value) && array_key_exists($token['value'], $value)) {
                        $next[] = $value[$token['value']];
                    }
                    break;

                case 'wildcard':
                    if (is_array($value)) {
                        foreach ($value as $item) {
                            $next[] = $item;
                        }
                    }
                    break;

                case 'slice':
                    if (is_array($value) && array_is_list($value)) {
                        $sliceRange = $token['value'];
                        $start = is_array($sliceRange) ? $sliceRange[0] : 0;
                        $end = is_array($sliceRange) && $sliceRange[1] !== null ? $sliceRange[1] : count($value);
                        $sliced = array_slice($value, $start, $end - $start);

                        foreach ($sliced as $item) {
                            $next[] = $item;
                        }
                    }
                    break;

                case 'recursive':
                    $next = array_merge($next, self::recursiveDescend($value, (string) $token['value']));
                    break;
            }
        }

        return self::resolve($next, $tokens);
    }

    /**
     * Recursively descend into data to find all values matching a key.
     *
     * @return array<mixed>
     */
    private static function recursiveDescend(mixed $data, string $key): array
    {
        $results = [];

        if (! is_array($data)) {
            return $results;
        }

        if (array_key_exists($key, $data)) {
            $results[] = $data[$key];
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $results = array_merge($results, self::recursiveDescend($value, $key));
            }
        }

        return $results;
    }
}
