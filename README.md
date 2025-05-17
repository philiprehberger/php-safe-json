# PHP Safe JSON

[![Tests](https://github.com/philiprehberger/php-safe-json/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-safe-json/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-safe-json.svg)](https://packagist.org/packages/philiprehberger/php-safe-json)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/php-safe-json)](https://github.com/philiprehberger/php-safe-json/commits/main)

Safe JSON parsing with exceptions, schema validation, and typed getters.

## Requirements

- PHP 8.2+

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

### Nullable Accessors

```php
$obj = SafeJson::decode('{"name":"Alice","age":30}');

$obj->stringOrNull('name');    // "Alice"
$obj->stringOrNull('missing'); // null
$obj->intOrNull('name');       // null (wrong type)
$obj->intOrNull('age');        // 30
```

### Merging Objects

```php
$a = SafeJson::decode('{"name":"Alice","age":30}');
$b = SafeJson::decode('{"name":"Bob","email":"bob@example.com"}');

$merged = $a->merge($b);
$merged->string('name');  // "Bob" (overridden)
$merged->int('age');      // 30 (kept from $a)
$merged->string('email'); // "bob@example.com" (added from $b)
```

### JSON Path Querying

```php
$obj = SafeJson::decode('{"users":[{"name":"Alice","age":30},{"name":"Bob","age":25}]}');

$obj->query('$.users[*].name');       // ['Alice', 'Bob']
$obj->query('$.users[0].age');        // [30]
$obj->query('$..name');               // ['Alice', 'Bob'] (recursive descent)
$obj->query('$.users[0:1]');          // [['name' => 'Alice', 'age' => 30]]
```

Supported syntax: `$` (root), `.key` (child), `[0]` (index), `[*]` (wildcard), `..key` (recursive descent), `[0:3]` (slice), `['key']` (bracket notation).

### JSON Diffing

```php
$changes = SafeJson::diff(
    '{"name":"Alice","age":30}',
    '{"name":"Bob","age":30,"email":"bob@example.com"}'
);

// [
//   ['op' => 'replace', 'path' => 'name', 'value' => 'Bob', 'old' => 'Alice'],
//   ['op' => 'add', 'path' => 'email', 'value' => 'bob@example.com'],
// ]
```

### Streaming Decode

```php
// Memory-efficient decoding of large JSON arrays
foreach (SafeJson::decodeStream('/path/to/large-file.json') as $element) {
    // Each element is decoded one at a time
    // Only one element is held in memory
}
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
| `diff(string $jsonA, string $jsonB): array` | Compare two JSON strings and return differences |
| `decodeStream(string $filePath): Generator` | Stream-decode a JSON array file, yielding elements one at a time |

### `JsonObject`

| Method | Description |
|---|---|
| `string(string $key): string` | Get string value by key |
| `int(string $key): int` | Get integer value by key |
| `float(string $key): float` | Get float value by key (accepts integers) |
| `bool(string $key): bool` | Get boolean value by key |
| `stringOrNull(string $key): ?string` | Get string value or null if missing/wrong type |
| `intOrNull(string $key): ?int` | Get integer value or null if missing/wrong type |
| `floatOrNull(string $key): ?float` | Get float value or null if missing/wrong type |
| `boolOrNull(string $key): ?bool` | Get boolean value or null if missing/wrong type |
| `array(string $key): array` | Get array value by key |
| `object(string $key): JsonObject` | Get nested JsonObject by key |
| `get(string $key, mixed $default = null): mixed` | Get value without type enforcement |
| `has(string $key): bool` | Check if key exists |
| `merge(self $other): self` | Merge with another JsonObject (other overrides on conflict) |
| `query(string $path): array` | Query data using JSON Path expression |
| `toArray(): array` | Return underlying array |
| `toJson(int $flags = 0): string` | Return JSON string |

All key-based methods support dot notation for nested access (e.g., `user.address.city`).

### `JsonPath`

| Method | Description |
|---|---|
| `query(array $data, string $path): array` | Query data using JSON Path expression |

### `JsonDiff`

| Method | Description |
|---|---|
| `diff(mixed $a, mixed $b, string $path = ''): array` | Compare two values and return list of differences |

### `StreamDecoder`

| Method | Description |
|---|---|
| `decodeStream(string $filePath): Generator` | Stream-decode a JSON array file element by element |

### Exceptions

| Exception | Thrown When |
|---|---|
| `JsonDecodeException` | Invalid JSON input |
| `JsonEncodeException` | Failed to encode data |
| `JsonKeyException` | Missing key or type mismatch |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/php-safe-json)

🐛 [Report issues](https://github.com/philiprehberger/php-safe-json/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/php-safe-json/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
