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
use Monolog\Formatter\FormatterInterface;

/**
 * Class StderrHandler
 * A handler that writes error log messages to stderr.
 * Handles WARNING (300) to EMERGENCY (600) levels by default, or a narrower range if
 * `general/logging/log_level` is configured above 300 in the admin panel.
 */
class StderrHandler extends AbstractLevelRangeHandler
{
    /**
     * Minimum log level (WARNING = 300)
     */
    private const MIN_LEVEL = 300;

    /**
     * Maximum log level (EMERGENCY = 600)
     */
    private const MAX_LEVEL = 600;

    public function __construct(FormatterInterface $formatter, ?ScopeConfigInterface $scopeConfig = null)
    {
        parent::__construct('php://stderr', self::MIN_LEVEL, self::MAX_LEVEL, $formatter, $scopeConfig);
    }
}
