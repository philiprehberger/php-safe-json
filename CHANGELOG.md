# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-03-13

### Added
- `SafeJson::decode()` and `SafeJson::tryDecode()` for safe JSON parsing
- `SafeJson::encode()` and `SafeJson::tryEncode()` for safe JSON encoding
- `JsonObject` with typed getters: `string()`, `int()`, `float()`, `bool()`, `array()`, `object()`
- Dot notation support for nested key access
- `JsonObject::get()` with optional default values
- `JsonObject::has()` for key existence checks
- `JsonObject::toArray()` and `JsonObject::toJson()` for serialization
- `JsonSerializable` and `Stringable` implementations on `JsonObject`
- `JsonDecodeException`, `JsonEncodeException`, and `JsonKeyException` for granular error handling
