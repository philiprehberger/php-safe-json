<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Tests;

use PhilipRehberger\SafeJson\JsonDiff;
use PhilipRehberger\SafeJson\SafeJson;
use PHPUnit\Framework\TestCase;

class JsonDiffTest extends TestCase
{
    public function test_identical_returns_empty(): void
    {
        $data = ['name' => 'Alice', 'age' => 30];

        $result = JsonDiff::diff($data, $data);

        $this->assertSame([], $result);
    }

    public function test_added_key(): void
    {
        $a = ['name' => 'Alice'];
        $b = ['name' => 'Alice', 'age' => 30];

        $result = JsonDiff::diff($a, $b);

        $this->assertCount(1, $result);
        $this->assertSame('add', $result[0]['op']);
        $this->assertSame('age', $result[0]['path']);
        $this->assertSame(30, $result[0]['value']);
    }

    public function test_removed_key(): void
    {
        $a = ['name' => 'Alice', 'age' => 30];
        $b = ['name' => 'Alice'];

        $result = JsonDiff::diff($a, $b);

        $this->assertCount(1, $result);
        $this->assertSame('remove', $result[0]['op']);
        $this->assertSame('age', $result[0]['path']);
        $this->assertSame(30, $result[0]['old']);
    }

    public function test_replaced_value(): void
    {
        $a = ['name' => 'Alice'];
        $b = ['name' => 'Bob'];

        $result = JsonDiff::diff($a, $b);

        $this->assertCount(1, $result);
        $this->assertSame('replace', $result[0]['op']);
        $this->assertSame('name', $result[0]['path']);
        $this->assertSame('Bob', $result[0]['value']);
        $this->assertSame('Alice', $result[0]['old']);
    }

    public function test_nested_changes(): void
    {
        $a = ['user' => ['name' => 'Alice', 'age' => 30]];
        $b = ['user' => ['name' => 'Alice', 'age' => 31]];

        $result = JsonDiff::diff($a, $b);

        $this->assertCount(1, $result);
        $this->assertSame('replace', $result[0]['op']);
        $this->assertSame('user.age', $result[0]['path']);
        $this->assertSame(31, $result[0]['value']);
        $this->assertSame(30, $result[0]['old']);
    }

    public function test_safe_json_diff(): void
    {
        $result = SafeJson::diff(
            '{"name":"Alice","age":30}',
            '{"name":"Bob","age":30}'
        );

        $this->assertCount(1, $result);
        $this->assertSame('replace', $result[0]['op']);
        $this->assertSame('name', $result[0]['path']);
    }

    public function test_multiple_changes(): void
    {
        $a = ['a' => 1, 'b' => 2, 'c' => 3];
        $b = ['a' => 1, 'b' => 99, 'd' => 4];

        $result = JsonDiff::diff($a, $b);

        $ops = array_column($result, 'op');

        $this->assertContains('replace', $ops);
        $this->assertContains('remove', $ops);
        $this->assertContains('add', $ops);
    }
}
