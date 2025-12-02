# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Added support for Monolog 3.x (required for Magento 2.4.8)
- Updated `composer.json` to allow `monolog/monolog: ^2.0 || ^3.0`
- Updated `StdoutHandler` to handle both Monolog 2.x and 3.x log level formats
- Updated `LogLevel` source model to use Monolog `Level` enum when available (Monolog 3.x)
- Backward compatible with Magento 2.4.6 and 2.4.7 (Monolog 2.x)

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
