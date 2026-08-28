<?php
/**
 * Copyright (c) 2024 Mohamed EL Mrabet
 * CleatSquad - https://cleatsquad.dev
 *
 * This file is part of the CleatSquad_LogStream module.
 * Licensed under the MIT License. See the LICENSE file in the module root.
 */
declare(strict_types=1);

namespace CleatSquad\LogStream\Logger;

use Monolog\Formatter\FormatterInterface;

/**
 * Class StdoutHandler
 * A handler that writes log messages to stdout.
 * Only handles DEBUG (100) to INFO (200) levels.
 */
class StdoutHandler extends AbstractLevelRangeHandler
{
    /**
     * Minimum log level (DEBUG = 100)
     */
    private const MIN_LEVEL = 100;

    /**
     * Maximum log level (INFO = 200)
     */
    private const MAX_LEVEL = 200;

    public function __construct(FormatterInterface $formatter)
    {
        parent::__construct('php://stdout', self::MIN_LEVEL, self::MAX_LEVEL, $formatter);
    }
}
