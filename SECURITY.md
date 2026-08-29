# Security Policy

## Supported Versions

Only the latest released version of `cleatsquad/magento2-logstream` receives
security fixes.

| Version | Supported |
| ------- | --------- |
| latest  | ✅        |
| < latest | ❌       |

## Reporting a Vulnerability

If you discover a security vulnerability in this module, please **do not**
open a public GitHub issue.

Instead, report it privately via
[GitHub Security Advisories](https://github.com/cleatsquad/magento2-logstream/security/advisories/new)
for this repository.

Please include:

- A description of the vulnerability and its potential impact
- Steps to reproduce it (Magento version, PHP version, module version)
- Any relevant logs or proof-of-concept code

You should receive an initial response within a few business days. Once a
fix is available, a new release will be published and the advisory will be
disclosed.

## Scope

This module writes Magento log records to `php://stdout` / `php://stderr`.
It does not handle authentication, payment data, or customer PII beyond
whatever an application chooses to log through Magento's own logger — the
module has no control over what callers pass into log context. Treat any
sensitive data appearing in your own log statements as your application's
responsibility, not this module's.
