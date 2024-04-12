<?php

declare(strict_types=1);

namespace CleatSquad\LogStream\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class StdoutHandler
 * A handler that writes log messages to stdout.
 */
class StdoutHandler extends StreamHandler
{
    /**
     * The path to the log level setting in the Magento configuration.
     */
    private const GENERAL_SETTINGS_LOGGING_LOG_LEVEL = 'general/logging/log_level';

    public function __construct(ScopeConfigInterface $scopeConfig, FormatterInterface $formatter)
    {
        $level = $this->getConfigLevel($scopeConfig);
        parent::__construct("php://stdout", $level, false);
        $this->setFormatter($formatter);
    }

    /**
     * Get the log level from the Magento configuration.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @return int
     */
    private function getConfigLevel(ScopeConfigInterface $scopeConfig): int
    {
        $level = $scopeConfig->getValue(
            self::GENERAL_SETTINGS_LOGGING_LOG_LEVEL,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $level ? (int)$level : Logger::INFO;
    }
}
