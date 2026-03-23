<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Tests;

use PhilipRehberger\SafeJson\Exceptions\JsonDecodeException;
use PhilipRehberger\SafeJson\Exceptions\JsonKeyException;
use PhilipRehberger\SafeJson\JsonObject;
use PhilipRehberger\SafeJson\SafeJson;
use PHPUnit\Framework\TestCase;

class SafeJsonTest extends TestCase
{
    public function test_decode_valid_json(): void
    {
        $obj = SafeJson::decode('{"name":"Alice","age":30}');

        $this->assertInstanceOf(JsonObject::class, $obj);
        $this->assertSame('Alice', $obj->string('name'));
        $this->assertSame(30, $obj->int('age'));
    }

    public function test_decode_invalid_json_throws(): void
    {
        $this->expectException(JsonDecodeException::class);

        SafeJson::decode('{invalid json}');
    }

    public function test_try_decode_returns_null_on_invalid(): void
    {
        $result = SafeJson::tryDecode('{not valid}');

        $this->assertNull($result);
    }

    public function test_try_decode_returns_object_on_valid(): void
    {
        $result = SafeJson::tryDecode('{"ok":true}');

        $this->assertInstanceOf(JsonObject::class, $result);
        $this->assertTrue($result->bool('ok'));
    }

    public function test_encode(): void
    {
        $json = SafeJson::encode(['key' => 'value']);

        $this->assertSame('{"key":"value"}', $json);
    }

    public function test_encode_with_flags(): void
    {
        $json = SafeJson::encode(['key' => 'value'], JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }

    public function test_try_encode_returns_null_on_failure(): void
    {
        $resource = fopen('php://memory', 'r');
        $result = SafeJson::tryEncode($resource);
        fclose($resource);

        $this->assertNull($result);
    }

    public function test_string_getter(): void
    {
        $obj = SafeJson::decode('{"name":"Bob"}');

        $this->assertSame('Bob', $obj->string('name'));
    }

    public function test_string_getter_type_mismatch(): void
    {
        $obj = SafeJson::decode('{"name":123}');

        $this->expectException(JsonKeyException::class);
        $this->expectExceptionMessage("expected type 'string'");

        $obj->string('name');
    }

    public function test_int_getter(): void
    {
        $obj = SafeJson::decode('{"count":42}');

        $this->assertSame(42, $obj->int('count'));
    }

    public function test_float_getter(): void
    {
        $obj = SafeJson::decode('{"price":9.99}');

        $this->assertSame(9.99, $obj->float('price'));
    }

    public function test_float_getter_accepts_int(): void
    {
        $obj = SafeJson::decode('{"price":10}');

        $this->assertSame(10.0, $obj->float('price'));
    }

    public function test_bool_getter(): void
    {
        $obj = SafeJson::decode('{"active":true}');

        $this->assertTrue($obj->bool('active'));
    }

    public function test_array_getter(): void
    {
        $obj = SafeJson::decode('{"tags":["a","b","c"]}');

        $this->assertSame(['a', 'b', 'c'], $obj->array('tags'));
    }

    public function test_object_getter(): void
    {
        $obj = SafeJson::decode('{"user":{"name":"Eve"}}');
        $user = $obj->object('user');

        $this->assertInstanceOf(JsonObject::class, $user);
        $this->assertSame('Eve', $user->string('name'));
    }

    public function test_dot_notation_access(): void
    {
        $obj = SafeJson::decode('{"user":{"address":{"city":"Vienna"}}}');

        $this->assertSame('Vienna', $obj->string('user.address.city'));
    }

    public function test_missing_key_throws(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->expectException(JsonKeyException::class);
        $this->expectExceptionMessage("Key 'missing' does not exist");

        $obj->get('missing');
    }

    public function test_get_with_default(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertSame('fallback', $obj->get('missing', 'fallback'));
    }

    public function test_get_with_null_default(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertNull($obj->get('missing', null));
    }

    public function test_has_returns_true_for_existing_key(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertTrue($obj->has('name'));
    }

    public function test_has_returns_false_for_missing_key(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertFalse($obj->has('age'));
    }

    public function test_has_with_dot_notation(): void
    {
        $obj = SafeJson::decode('{"user":{"name":"Alice"}}');

        $this->assertTrue($obj->has('user.name'));
        $this->assertFalse($obj->has('user.email'));
    }

    public function test_to_array(): void
    {
        $data = ['name' => 'Alice', 'age' => 30];
        $obj = SafeJson::decode(json_encode($data));

        $this->assertSame($data, $obj->toArray());
    }

    public function test_to_json(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertSame('{"name":"Alice"}', $obj->toJson());
    }

    public function test_json_serializable(): void
    {
        $obj = SafeJson::decode('{"key":"value"}');

        $this->assertSame('{"key":"value"}', json_encode($obj));
    }

    public function test_stringable(): void
    {
        $obj = SafeJson::decode('{"key":"value"}');

        $this->assertSame('{"key":"value"}', (string) $obj);
    }

    public function test_string_or_null_returns_value(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertSame('Alice', $obj->stringOrNull('name'));
    }

    public function test_string_or_null_returns_null_for_missing_key(): void
    {
        $obj = SafeJson::decode('{"name":"Alice"}');

        $this->assertNull($obj->stringOrNull('missing'));
    }

    public function test_string_or_null_returns_null_for_wrong_type(): void
    {
        $obj = SafeJson::decode('{"name":123}');

        $this->assertNull($obj->stringOrNull('name'));
    }

    public function test_int_or_null_returns_value(): void
    {
        $obj = SafeJson::decode('{"count":42}');

        $this->assertSame(42, $obj->intOrNull('count'));
    }

    public function test_int_or_null_returns_null_for_missing_key(): void
    {
        $obj = SafeJson::decode('{"count":42}');

        $this->assertNull($obj->intOrNull('missing'));
    }

    public function test_int_or_null_returns_null_for_wrong_type(): void
    {
        $obj = SafeJson::decode('{"count":"not a number"}');

        $this->assertNull($obj->intOrNull('count'));
    }

    public function test_float_or_null_returns_value(): void
    {
        $obj = SafeJson::decode('{"price":9.99}');

        $this->assertSame(9.99, $obj->floatOrNull('price'));
    }

    public function test_float_or_null_returns_null_for_missing_key(): void
    {
        $obj = SafeJson::decode('{"price":9.99}');

        $this->assertNull($obj->floatOrNull('missing'));
    }

    public function test_float_or_null_returns_null_for_wrong_type(): void
    {
        $obj = SafeJson::decode('{"price":"expensive"}');

        $this->assertNull($obj->floatOrNull('price'));
    }

    public function test_bool_or_null_returns_value(): void
    {
        $obj = SafeJson::decode('{"active":true}');

        $this->assertTrue($obj->boolOrNull('active'));
    }

    public function test_bool_or_null_returns_null_for_missing_key(): void
    {
        $obj = SafeJson::decode('{"active":true}');

        $this->assertNull($obj->boolOrNull('missing'));
    }

    public function test_bool_or_null_returns_null_for_wrong_type(): void
    {
        $obj = SafeJson::decode('{"active":"yes"}');

        $this->assertNull($obj->boolOrNull('active'));
    }

    public function test_merge_with_overlapping_keys(): void
    {
        $a = SafeJson::decode('{"name":"Alice","age":30}');
        $b = SafeJson::decode('{"name":"Bob","email":"bob@example.com"}');

        $merged = $a->merge($b);

        $this->assertSame('Bob', $merged->string('name'));
        $this->assertSame(30, $merged->int('age'));
        $this->assertSame('bob@example.com', $merged->string('email'));
    }

    public function test_merge_with_non_overlapping_keys(): void
    {
        $a = SafeJson::decode('{"name":"Alice"}');
        $b = SafeJson::decode('{"age":25}');

        $merged = $a->merge($b);

        $this->assertSame('Alice', $merged->string('name'));
        $this->assertSame(25, $merged->int('age'));
    }
}
