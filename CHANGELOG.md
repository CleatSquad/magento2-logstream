# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
