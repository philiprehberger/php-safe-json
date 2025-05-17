# Changelog

All notable changes to `php-safe-json` will be documented in this file.

## [Unreleased]

## [1.2.0] - 2026-04-01

### Added
- JSON Path querying via `JsonObject::query()` and `JsonPath::query()`
- JSON diffing via `SafeJson::diff()` with add/remove/replace operations
- Streaming JSON array decode via `SafeJson::decodeStream()` for large files

## [1.1.1] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility
- Add GitHub issue templates, dependabot config, and PR template

## [1.1.0] - 2026-03-22

### Added
- Nullable accessor methods: `stringOrNull()`, `intOrNull()`, `floatOrNull()`, `boolOrNull()`
- `merge()` method for combining two JsonObject instances

## [1.0.2] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.1] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

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
