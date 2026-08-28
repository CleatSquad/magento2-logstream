# CleatSquad Magento 2 LogStream

A Magento 2 module that redirects all Magento logs to **StdOut/StdErr**, making it ideal for **Docker** and **containerized environments**. This enables seamless log aggregation into external systems (ELK, Datadog, CloudWatch, etc.) without managing Magento-specific log files.

## Badges

[![Packagist Downloads](https://img.shields.io/packagist/dm/cleatsquad/magento2-logstream?color=blue)](https://packagist.org/packages/cleatsquad/magento2-logstream/stats)
[![Packagist Version](https://img.shields.io/packagist/v/cleatsquad/magento2-logstream?color=blue)](https://packagist.org/packages/cleatsquad/magento2-logstream)
[![Packagist License](https://img.shields.io/packagist/l/cleatsquad/magento2-logstream)](https://github.com/cleatsquad/magento2-logstream/blob/master/LICENSE.md)
![Magento 2.4.6 and above](https://img.shields.io/badge/Magento-2.4.6%20--%202.4.8-brightgreen.svg?style=flat)
![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-blue.svg?style=flat)

---

## ✨ Features

- 🐳 **Docker-ready**: Logs to StdOut/StdErr for seamless container integration.
- 📊 **External Log Aggregation**: Works with ELK, Datadog, CloudWatch, Splunk, etc.
- 🔀 **Stdout/Stderr Split**: DEBUG-INFO goes to `php://stdout`, WARNING-EMERGENCY goes to `php://stderr` — matching how log collectors and `docker logs` typically separate output.
- 🎨 **Colored or JSON Output**: Ships with a colored terminal formatter by default, and a structured JSON formatter for Kubernetes/New Relic/Datadog, selectable via `di.xml`.
- 🛡️ **Clean DI Override**: Uses Magento dependency injection, no core hacks.
- 🎯 **Zero Configuration**: Works out of the box after installation.
- 🔄 **Real-time Logs**: Immediate log output without file I/O delays.
- 🧩 **Monolog 2.x & 3.x Integration**: Built on Monolog's StreamHandler, compatible with both major versions.

---

## 📦 Installation

You can install this module using Composer (recommended) or manually.

---

### 🔹 1. Install via Composer (recommended)

1. **Download the package**
    ```bash
    composer require cleatsquad/magento2-logstream
    ```

2. **Enable the module**
    ```bash
    bin/magento module:enable CleatSquad_LogStream
    bin/magento setup:upgrade
    ```

---

### 🔹 2. Manual Installation (app/code)

1. **Copy the module to your Magento installation**
    ```
    app/code/CleatSquad/LogStream/
    ```

2. **Enable the module**
    ```bash
    bin/magento module:enable CleatSquad_LogStream
    bin/magento setup:upgrade
    ```

---

## 🚀 Usage

Once installed, the module will automatically redirect all Magento logs to StdOut without any additional configuration.

### Viewing Logs in Docker

```bash
docker logs -f <container_name>
```

### Example Log Output

```
[2024-01-15 10:30:45] main.INFO: User login successful {"username":"admin"} []
[2024-01-15 10:30:46] main.WARNING: Cache miss for product 123 [] []
```

---

## ⚙️ Configuration

Log routing is split by severity between two streams, matching how log collectors and
`docker logs` typically separate output:

| Level | Value | Default Stream |
|-------|-------|--------|
| DEBUG | 100 | `php://stdout` |
| INFO | 200 | `php://stdout` |
| NOTICE | 250 | `php://stderr` |
| WARNING | 300 | `php://stderr` |
| ERROR | 400 | `php://stderr` |
| CRITICAL | 500 | `php://stderr` |
| ALERT | 550 | `php://stderr` |
| EMERGENCY | 600 | `php://stderr` |

### Setting a Minimum Log Level

`Stores > Configuration > General > Logging > Minimum Log Level` lets you raise the
severity threshold above each stream's own baseline — e.g. set it to `WARNING` to silence
`DEBUG`/`INFO` on stdout entirely, or to `ERROR` to also drop `WARNING`/`NOTICE` from stderr.
It cannot lower the threshold below a stream's own range: stdout will never emit
`WARNING`-and-above, and stderr will never emit below `WARNING`, regardless of this setting.
The default (`DEBUG`) logs everything, matching the zero-configuration behavior described
above. Changes take effect immediately, no cache flush required.

### Choosing a Formatter

The module ships with two formatters, wired in the module's own `etc/di.xml`:

- **`ColoredLineFormatter`** (default): human-readable, ANSI-colored single-line output for
  local development and plain-text log viewers. Automatically appends a stack trace to
  WARNING-and-above entries that don't already carry an exception.
- **`JsonStreamFormatter`**: structured JSON output with fields pre-mapped for Kubernetes,
  New Relic, and Datadog (`service`, `environment`, `severity`, `trace_id`, `kubernetes.*`, etc.).

To switch to the JSON formatter, add an `app/etc/di.xml` in your project:

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="CleatSquad\LogStream\Logger\StdoutHandler">
        <arguments>
            <argument name="formatter" xsi:type="object">CleatSquad\LogStream\Logger\Formatter\JsonStreamFormatter</argument>
        </arguments>
    </type>
    <type name="CleatSquad\LogStream\Logger\StderrHandler">
        <arguments>
            <argument name="formatter" xsi:type="object">CleatSquad\LogStream\Logger\Formatter\JsonStreamFormatter</argument>
        </arguments>
    </type>
</config>
```

`JsonStreamFormatter` accepts `serviceName`, `environment`, and `includeStackTrace` constructor
arguments (see `src/etc/di.xml` for the defaults) — override them the same way if needed.

---

## 🔧 Technical Details

### Architecture

This module works by:

1. **Overriding Monolog's handlers**: Registers `StdoutHandler` and `StderrHandler` on
   Magento's logger via DI, replacing the default file handler.
2. **Splitting by severity**: `StdoutHandler` only handles DEBUG-INFO, `StderrHandler` only
   handles WARNING-EMERGENCY — each checks the record's level against its own range, and
   against the admin-configured minimum level, in `isHandling()`.
3. **Reading configuration lazily**: the admin log level is read from `ScopeConfigInterface`
   only when a record is being handled, never in the constructor, and any failure falls back
   to the handler's baseline range — this keeps `bin/magento setup:install` safe, since the
   handler is built before the database connection exists.
4. **Streaming directly**: All log messages are written straight to `php://stdout` /
   `php://stderr`, with no intermediate log file.

### DI Configuration

```xml
<type name="Magento\Framework\Logger\Monolog">
    <arguments>
        <argument name="handlers" xsi:type="array">
            <item name="stdout" xsi:type="object">CleatSquad\LogStream\Logger\StdoutHandler</item>
            <item name="stderr" xsi:type="object">CleatSquad\LogStream\Logger\StderrHandler</item>
        </argument>
    </arguments>
</type>
```

---

## 🔄 Upgrading

To upgrade the module to the latest version, run:

```bash
composer update cleatsquad/magento2-logstream
bin/magento setup:upgrade
```

---

## 📋 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/cleatsquad/magento2-logstream/tags).

---

## 🔗 Follow

For the latest updates and new features, follow our GitHub repository: [cleatsquad/magento2-logstream](https://github.com/cleatsquad/magento2-logstream).

---

## 🤝 Contributing

Contributions to `CleatSquad_LogStream` are always welcome. You can contribute in different ways:

1. **Report Issues**: Report bugs and suggest new features.
2. **Fix Bugs**: Submit pull requests with bug fixes.
3. **Add Features**: Develop new features and submit them as pull requests.
4. **Improve Documentation**: Help new users by improving or translating the documentation.

Issues and pull requests are welcome.

GitHub: https://github.com/CleatSquad/magento2-logstream

---

## 💬 Support

If you need help or have a question, you can:

- Open an issue through GitHub for bug reports and feature requests.
- Check the [Magento Community Forums](https://community.magento.com/) for general questions and support on Magento.
- Check on [Magento Stack Exchange](https://magento.stackexchange.com/) for general programming questions.

---

## 👤 Authors

- **Mohamed EL Mrabet** - *Initial work* - [mimou78](https://github.com/mimou78)

See also the list of [contributors](https://github.com/cleatsquad/magento2-logstream/contributors) who participated in this project.

---

## 📜 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

---

## 🙏 Acknowledgments

This module is powered by the excellent Monolog library:

➡️ https://github.com/Seldaek/monolog

- Magento Community
- Anyone who contributes to the open-source community

---

© 2024 - CleatSquad (https://cleatsquad.dev)
