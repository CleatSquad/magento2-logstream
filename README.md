# CleatSquad Magento 2 LogStream

A Magento 2 module that redirects all Magento logs to **StdOut**, making it ideal for **Docker** and **containerized environments**. This enables seamless log aggregation into external systems (ELK, Datadog, CloudWatch, etc.) without managing Magento-specific log files.

## Badges

[![Packagist Downloads](https://img.shields.io/packagist/dm/cleatsquad/magento2-logstream?color=blue)](https://packagist.org/packages/cleatsquad/magento2-logstream/stats)
[![Packagist Version](https://img.shields.io/packagist/v/cleatsquad/magento2-logstream?color=blue)](https://packagist.org/packages/cleatsquad/magento2-logstream)
[![Packagist License](https://img.shields.io/packagist/l/cleatsquad/magento2-logstream)](https://github.com/cleatsquad/magento2-logstream/blob/master/LICENSE.md)
![Magento 2.4.6 and above](https://img.shields.io/badge/Magento-2.4.6%20--%202.4.8-brightgreen.svg?style=flat)
![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-blue.svg?style=flat)

---

## ✨ Features

- 🐳 **Docker-ready**: Logs to StdOut for seamless container integration.
- 📊 **External Log Aggregation**: Works with ELK, Datadog, CloudWatch, Splunk, etc.
- ⚙️ **Configurable Log Levels**: Set log level (DEBUG, INFO, WARNING, ERROR, etc.) from admin panel.
- 🛡️ **Clean DI Override**: Uses Magento dependency injection, no core hacks.
- 🎯 **Zero Configuration**: Works out of the box after installation.
- 🔄 **Real-time Logs**: Immediate log output without file I/O delays.
- 🧩 **Monolog Integration**: Built on Monolog's StreamHandler.

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

To configure the log level for the CleatSquad Magento2 LogStream module, follow these steps in the Magento admin interface:

### Setting the Log Level

1. Navigate to `Stores > Configuration` in the admin panel sidebar.
2. Under the `General` section, find and open the `Logging` group.
3. Select the desired log level from the `Log Level` dropdown menu.

### Available Log Levels

| Level | Value | Description |
|-------|-------|-------------|
| DEBUG | 100 | Detailed debug information |
| INFO | 200 | Interesting events (default) |
| NOTICE | 250 | Normal but significant events |
| WARNING | 300 | Exceptional occurrences that are not errors |
| ERROR | 400 | Runtime errors |
| CRITICAL | 500 | Critical conditions |
| ALERT | 550 | Action must be taken immediately |
| EMERGENCY | 600 | System is unusable |

Changes will take effect immediately after saving the configuration.

---

## 🔧 Technical Details

### Architecture

This module works by:

1. **Overriding Monolog's handler**: Adds a custom `StdoutHandler` to Magento's logger via DI.
2. **Streaming to php://stdout**: All log messages are written directly to standard output.
3. **Respecting log levels**: Only logs at or above the configured level are output.

### DI Configuration

```xml
<type name="Magento\Framework\Logger\Monolog">
    <arguments>
        <argument name="handlers" xsi:type="array">
            <item name="stdout" xsi:type="object">CleatSquad\LogStream\Logger\StdoutHandler</item>
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
