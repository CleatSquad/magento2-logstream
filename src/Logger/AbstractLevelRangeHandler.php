<?php

/**
 * Copyright (c) 2025 Mohamed EL Mrabet
 * CleatSquad - https://cleatsquad.dev
 *
 * This file is part of the CleatSquad_LogStream module.
 * Licensed under the MIT License. See the LICENSE file in the module root.
 */
declare(strict_types=1);

namespace CleatSquad\LogStream\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;

/**
 * Class AbstractLevelRangeHandler
 * A stream handler that only handles log records within a [minLevel, maxLevel] range.
 *
 * The admin-configurable `general/logging/log_level` setting, when available, can raise the
 * effective minimum level above the handler's own baseline (e.g. to silence DEBUG/INFO
 * noise), but never below it or above the handler's maximum, preserving the stdout/stderr
 * split. The setting is read lazily, on each isHandling() call, and never in the
 * constructor: this handler is instantiated while building Magento's logger, which happens
 * before the DB connection is available during `bin/magento setup:install` — reading scope
 * config at construction time crashes the installer.
 */
abstract class AbstractLevelRangeHandler extends StreamHandler
{
    /**
     * Path to the admin-configurable minimum log level setting
     */
    private const CONFIG_PATH = 'general/logging/log_level';

    private int $baselineMinLevel;

    private int $maxLevel;

    private ?ScopeConfigInterface $scopeConfig;

    public function __construct(
        string $stream,
        int $minLevel,
        int $maxLevel,
        FormatterInterface $formatter,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        parent::__construct($stream, $minLevel, false);
        $this->baselineMinLevel = $minLevel;
        $this->maxLevel = $maxLevel;
        $this->scopeConfig = $scopeConfig;
        $this->setFormatter($formatter);
    }

    /**
     * Check if this handler handles the given log record.
     *
     * @param LogRecord|array $record
     * @return bool
     */
    public function isHandling(LogRecord|array $record): bool
    {
        // Get level value - handle both Monolog 2.x (array) and 3.x (LogRecord)
        $level = $record instanceof LogRecord ? $record->level->value : ($record['level'] ?? 0);

        $minLevel = max($this->baselineMinLevel, $this->resolveConfiguredMinLevel());

        return $level >= $minLevel && $level <= $this->maxLevel;
    }

    /**
     * Read the admin-configured minimum log level, if any.
     *
     * Never throws: scope config can be unavailable or fail (e.g. no DB connection yet
     * during setup:install), in which case the handler's own baseline applies unchanged.
     *
     * @return int
     */
    private function resolveConfiguredMinLevel(): int
    {
        if ($this->scopeConfig === null) {
            return $this->baselineMinLevel;
        }

        try {
            $configured = $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
        } catch (\Throwable $exception) {
            return $this->baselineMinLevel;
        }

        return $configured !== null && $configured !== '' ? (int)$configured : $this->baselineMinLevel;
    }
}
