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

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;

/**
 * Class AbstractLevelRangeHandler
 * A stream handler that only handles log records within a fixed [minLevel, maxLevel] range.
 */
abstract class AbstractLevelRangeHandler extends StreamHandler
{
    private int $minLevel;

    private int $maxLevel;

    public function __construct(string $stream, int $minLevel, int $maxLevel, FormatterInterface $formatter)
    {
        parent::__construct($stream, $minLevel, false);
        $this->minLevel = $minLevel;
        $this->maxLevel = $maxLevel;
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

        return $level >= $this->minLevel && $level <= $this->maxLevel;
    }
}
