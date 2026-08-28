# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- Restored the admin configuration option `general/logging/log_level` (`src/etc/adminhtml/system.xml` and `src/etc/config.xml`), removed earlier in this release to work around a `setup:install` crash. `ScopeConfigInterface` is now read lazily inside `isHandling()` — never in the constructor — and defensively wrapped in `try/catch`, so it can no longer fail during bootstrap while the DB isn't available yet. The configured value raises the effective minimum severity above each handler's baseline (stdout: DEBUG-INFO, stderr: WARNING-EMERGENCY) but can never widen it beyond that range. Verified against a live Magento 2.4.9 install: `setup:upgrade` and `setup:di:compile` complete without error, and raising the admin threshold correctly filters DEBUG/INFO out of stdout.

### Added
- `StderrHandler` to route WARNING-EMERGENCY logs to `php://stderr`, separate from `StdoutHandler`
- `ColoredLineFormatter` for ANSI-colored terminal output with automatic stack traces
- `JsonStreamFormatter` for structured JSON output compatible with Kubernetes, New Relic, and Datadog

### Changed
- Extracted the shared level-range filtering logic of `StdoutHandler` and `StderrHandler` into a common `AbstractLevelRangeHandler` base class
- Cleaned up the inactive JSON formatter block in `di.xml`
- Added support for Monolog 3.x (required for Magento 2.4.8)
- Updated `composer.json` to allow `monolog/monolog: ^2.0 || ^3.0`
- Updated `StdoutHandler` to handle both Monolog 2.x and 3.x log level formats
- Updated `LogLevel` source model to use Monolog `Level` enum when available (Monolog 3.x)
- Backward compatible with Magento 2.4.6 and 2.4.7 (Monolog 2.x)

### Documentation
- Documented the admin-configurable minimum log level and how it interacts with the stdout/stderr split
- Documented how to switch between `ColoredLineFormatter` and `JsonStreamFormatter` via `app/etc/di.xml`
- Updated the Features and Architecture sections for the stdout/stderr split and Monolog 2.x/3.x support

## [1.0.0] - 2024-01-15

### Added
- Initial release
- StdoutHandler for redirecting logs to php://stdout
- Configurable log levels via Magento admin panel
- Support for all Monolog log levels (DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)
- Clean DI override for Magento's Monolog logger
- Compatibility with Magento 2.4.6+
- PHP 8.1+ support

### Documentation
- README with installation, usage, and configuration instructions
- Technical architecture documentation
- Docker integration examples

---

[Unreleased]: https://github.com/cleatsquad/magento2-logstream/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/cleatsquad/magento2-logstream/releases/tag/v1.0.0
