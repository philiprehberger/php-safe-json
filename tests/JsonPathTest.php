<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Tests;

use PhilipRehberger\SafeJson\JsonPath;
use PhilipRehberger\SafeJson\SafeJson;
use PHPUnit\Framework\TestCase;

class JsonPathTest extends TestCase
{
    public function test_query_root_key(): void
    {
        $data = ['name' => 'Alice', 'age' => 30];

        $result = JsonPath::query($data, '$.name');

        $this->assertSame(['Alice'], $result);
    }

    public function test_query_nested_key(): void
    {
        $data = ['user' => ['address' => ['city' => 'Vienna']]];

        $result = JsonPath::query($data, '$.user.address.city');

        $this->assertSame(['Vienna'], $result);
    }

    public function test_query_array_index(): void
    {
        $data = ['items' => ['apple', 'banana', 'cherry']];

        $result = JsonPath::query($data, '$.items[0]');

        $this->assertSame(['apple'], $result);
    }

    public function test_query_wildcard(): void
    {
        $data = ['users' => [
            ['name' => 'Alice'],
            ['name' => 'Bob'],
            ['name' => 'Charlie'],
        ]];

        $result = JsonPath::query($data, '$.users[*].name');

        $this->assertSame(['Alice', 'Bob', 'Charlie'], $result);
    }

    public function test_query_recursive_descent(): void
    {
        $data = [
            'id' => 1,
            'child' => [
                'id' => 2,
                'nested' => [
                    'id' => 3,
                ],
            ],
        ];

        $result = JsonPath::query($data, '$..id');

        $this->assertSame([1, 2, 3], $result);
    }

    public function test_query_slice(): void
    {
        $data = ['items' => [10, 20, 30, 40, 50]];

        $result = JsonPath::query($data, '$.items[0:2]');

        $this->assertSame([10, 20], $result);
    }

    public function test_query_empty_result(): void
    {
        $data = ['name' => 'Alice'];

        $result = JsonPath::query($data, '$.missing');

        $this->assertSame([], $result);
    }

    public function test_query_bracket_notation(): void
    {
        $data = ['key with spaces' => 'value'];

        $result = JsonPath::query($data, "$['key with spaces']");

        $this->assertSame(['value'], $result);
    }

    public function test_json_object_query(): void
    {
        $obj = SafeJson::decode('{"users":[{"name":"Alice"},{"name":"Bob"}]}');

        $result = $obj->query('$.users[*].name');

        $this->assertSame(['Alice', 'Bob'], $result);
    }
}
