# php-safe-json

[![Tests](https://github.com/philiprehberger/php-safe-json/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-safe-json/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-safe-json.svg)](https://packagist.org/packages/philiprehberger/php-safe-json)
[![License](https://img.shields.io/packagist/l/philiprehberger/php-safe-json.svg)](https://packagist.org/packages/philiprehberger/php-safe-json)

Safe JSON parsing with exceptions and typed getters for PHP.

## Requirements

- PHP ^8.2

## Installation

```bash
composer require philiprehberger/php-safe-json
```

## Usage

### Decoding JSON

```php
use PhilipRehberger\SafeJson\SafeJson;

$obj = SafeJson::decode('{"name":"Alice","age":30,"active":true}');

$obj->string('name');   // "Alice"
$obj->int('age');       // 30
$obj->bool('active');   // true
```

### Dot Notation for Nested Access

```php
$obj = SafeJson::decode('{"user":{"address":{"city":"Vienna"}}}');

$obj->string('user.address.city'); // "Vienna"
$obj->has('user.address.city');    // true
$obj->has('user.address.zip');     // false
```

### Nested Objects

```php
$obj = SafeJson::decode('{"user":{"name":"Alice"}}');

$user = $obj->object('user');
$user->string('name'); // "Alice"
```

### Safe Decoding (No Exceptions)

```php
$obj = SafeJson::tryDecode('{invalid}');
// Returns null instead of throwing

$obj = SafeJson::tryDecode('{"valid":true}');
// Returns JsonObject
```

### Default Values

```php
$obj = SafeJson::decode('{"name":"Alice"}');

$obj->get('name');              // "Alice"
$obj->get('missing', 'default'); // "default"
$obj->get('missing');           // throws JsonKeyException
```

### Encoding

```php
$json = SafeJson::encode(['key' => 'value']);
// '{"key":"value"}'

$json = SafeJson::tryEncode($data);
// Returns null on failure instead of throwing
```

### Serialization

```php
$obj = SafeJson::decode('{"key":"value"}');

$obj->toArray(); // ['key' => 'value']
$obj->toJson();  // '{"key":"value"}'
json_encode($obj); // '{"key":"value"}' (JsonSerializable)
(string) $obj;     // '{"key":"value"}' (Stringable)
```

## API

### `SafeJson`

| Method | Description |
|---|---|
| `decode(string $json): JsonObject` | Decode JSON string, throws `JsonDecodeException` on failure |
| `tryDecode(string $json): ?JsonObject` | Decode JSON string, returns `null` on failure |
| `encode(mixed $data, int $flags = 0): string` | Encode to JSON string, throws on failure |
| `tryEncode(mixed $data, int $flags = 0): ?string` | Encode to JSON string, returns `null` on failure |

### `JsonObject`

| Method | Description |
|---|---|
| `string(string $key): string` | Get string value by key |
| `int(string $key): int` | Get integer value by key |
| `float(string $key): float` | Get float value by key (accepts integers) |
| `bool(string $key): bool` | Get boolean value by key |
| `array(string $key): array` | Get array value by key |
| `object(string $key): JsonObject` | Get nested JsonObject by key |
| `get(string $key, mixed $default = null): mixed` | Get value without type enforcement |
| `has(string $key): bool` | Check if key exists |
| `toArray(): array` | Return underlying array |
| `toJson(int $flags = 0): string` | Return JSON string |

All key-based methods support dot notation for nested access (e.g., `user.address.city`).

### Exceptions

| Exception | Thrown When |
|---|---|
| `JsonDecodeException` | Invalid JSON input |
| `JsonEncodeException` | Failed to encode data |
| `JsonKeyException` | Missing key or type mismatch |

## Testing

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT License. See [LICENSE](LICENSE) for details.
