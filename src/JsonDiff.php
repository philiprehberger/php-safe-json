<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson;

class JsonDiff
{
    /**
     * Compare two values and return a list of differences.
     *
     * Each difference is an associative array with:
     * - `op`: 'add', 'remove', or 'replace'
     * - `path`: the JSON pointer path to the changed value
     * - `value`: the new value (for 'add' and 'replace')
     * - `old`: the previous value (for 'remove' and 'replace')
     *
     * @return array<array{op: string, path: string, value?: mixed, old?: mixed}>
     */
    public static function diff(mixed $a, mixed $b, string $path = ''): array
    {
        if ($a === $b) {
            return [];
        }

        $aIsArray = is_array($a);
        $bIsArray = is_array($b);

        if (! $aIsArray || ! $bIsArray) {
            return [self::replacement($path, $a, $b)];
        }

        $aIsList = $a === [] || array_is_list($a);
        $bIsList = $b === [] || array_is_list($b);

        // If both are sequential arrays, compare by index
        if ($aIsList && $bIsList) {
            return self::diffLists($a, $b, $path);
        }

        // If one is a list and the other is an object, treat as replacement
        if ($aIsList !== $bIsList) {
            return [self::replacement($path, $a, $b)];
        }

        return self::diffObjects($a, $b, $path);
    }

    /**
     * Diff two associative arrays (objects).
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     * @return array<array{op: string, path: string, value?: mixed, old?: mixed}>
     */
    private static function diffObjects(array $a, array $b, string $path): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($a), array_keys($b)));

        foreach ($allKeys as $key) {
            $childPath = $path === '' ? $key : $path.'.'.$key;
            $inA = array_key_exists($key, $a);
            $inB = array_key_exists($key, $b);

            if ($inA && ! $inB) {
                $changes[] = ['op' => 'remove', 'path' => $childPath, 'old' => $a[$key]];
            } elseif (! $inA && $inB) {
                $changes[] = ['op' => 'add', 'path' => $childPath, 'value' => $b[$key]];
            } else {
                $changes = array_merge($changes, self::diff($a[$key], $b[$key], $childPath));
            }
        }

        return $changes;
    }

    /**
     * Diff two sequential arrays (lists).
     *
     * @param  array<int, mixed>  $a
     * @param  array<int, mixed>  $b
     * @return array<array{op: string, path: string, value?: mixed, old?: mixed}>
     */
    private static function diffLists(array $a, array $b, string $path): array
    {
        $changes = [];
        $maxLen = max(count($a), count($b));

        for ($i = 0; $i < $maxLen; $i++) {
            $childPath = $path === '' ? (string) $i : $path.'.'.$i;

            if ($i >= count($a)) {
                $changes[] = ['op' => 'add', 'path' => $childPath, 'value' => $b[$i]];
            } elseif ($i >= count($b)) {
                $changes[] = ['op' => 'remove', 'path' => $childPath, 'old' => $a[$i]];
            } else {
                $changes = array_merge($changes, self::diff($a[$i], $b[$i], $childPath));
            }
        }

        return $changes;
    }

    /**
     * Create a replacement entry.
     *
     * @return array{op: string, path: string, value: mixed, old: mixed}
     */
    private static function replacement(string $path, mixed $old, mixed $new): array
    {
        return ['op' => 'replace', 'path' => $path, 'value' => $new, 'old' => $old];
    }
}
