# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-08-29

### Fixed
- Restored the admin configuration option `general/logging/log_level` (`src/etc/adminhtml/system.xml` and `src/etc/config.xml`), removed earlier in this release to work around a `setup:install` crash. `ScopeConfigInterface` is now read lazily inside `isHandling()` — never in the constructor — and defensively wrapped in `try/catch`, so it can no longer fail during bootstrap while the DB isn't available yet. The configured value raises the effective minimum severity above each handler's baseline (stdout: DEBUG-INFO, stderr: WARNING-EMERGENCY) but can never widen it beyond that range. Verified against a live Magento 2.4.9 install: `setup:upgrade` and `setup:di:compile` complete without error, and raising the admin threshold correctly filters DEBUG/INFO out of stdout.
- Fixed the `phpstan` CI job, which was silently broken: it invoked `vendor/bin/phpstan analyse` with no path and no config file, so it would fail immediately with a usage error rather than actually analysing anything. Added `phpstan.neon.dist` (level 5, `paths: [src]`).
- Fixed the `php-cs-fixer` CI job, which was also broken: `friendsofphp/php-cs-fixer` was never declared as a dev dependency, so `vendor/bin/php-cs-fixer` did not exist. Added it to `require-dev` and a `.php-cs-fixer.dist.php` (`@PSR12`).
- Fixed a dead `mkdir -p "$HOME/.composer"` step in the CI workflow: `auth.json` was actually written to the job's working directory, not `$HOME/.composer/`, making that step a no-op. The CI's own container image additionally pins `COMPOSER_HOME=/var/www/.composer`, which differs from `$HOME/.composer` — so `auth.json` is now written to `${COMPOSER_HOME:-$HOME/.composer}`, resolved at runtime, to work regardless of the image's `$HOME`.
- Removed `--ignore-platform-reqs` from the `composer install` CI step. Verified with a real `composer install` against the CI's own container image (PHP 8.2, with valid `repo.magento.com` credentials): every dependency, including `magento/framework` and its full tree, installs cleanly with no platform-requirement warnings — the flag was only ever masking, not working around, a real incompatibility.
- Fixed `ColoredLineFormatter::hasException()`: a redundant `$context['exception'] instanceof \Throwable` check after a loop that already inspects every value of `$context` (including `'exception'`) — dead code that PHPStan (level 5) flagged as always `false`. Simplified to just the loop.
- Removed unused `MAX_LEVEL` test constants in `StdoutHandlerTest`/`StderrHandlerTest`, left over from the `AbstractLevelRangeHandler` extraction.
- Simplified two `$frame['function'] ?? 'unknown'` fallbacks in the stack-trace generators (`ColoredLineFormatter`, `JsonStreamFormatter`): `debug_backtrace()`'s `'function'` key is never absent, so the fallback was unreachable.

### Added
- `StderrHandler` to route WARNING-EMERGENCY logs to `php://stderr`, separate from `StdoutHandler`
- `ColoredLineFormatter` for ANSI-colored terminal output with automatic stack traces
- `JsonStreamFormatter` for structured JSON output compatible with Kubernetes, New Relic, and Datadog
- Added a `PHPUnit` CI job. Unit tests existed for every class in this module but were never actually run in CI — only PHPStan and PHP-CS-Fixer were wired up.

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

[Unreleased]: https://github.com/cleatsquad/magento2-logstream/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/cleatsquad/magento2-logstream/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/cleatsquad/magento2-logstream/releases/tag/v1.0.0
