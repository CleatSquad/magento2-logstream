<?php

/**
 * Copyright (c) 2025 Mohamed EL Mrabet
 * CleatSquad - https://cleatsquad.dev
 *
 * This file is part of the CleatSquad_LogStream module.
 * Licensed under the MIT License. See the LICENSE file in the module root.
 */
declare(strict_types=1);

namespace CleatSquad\LogStream\Logger\Formatter;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

/**
 * Class ColoredLineFormatter
 * A formatter that adds ANSI colors to log output for terminal display.
 * Also includes stack traces for error-level logs.
 */
class ColoredLineFormatter extends LineFormatter
{
    /**
     * ANSI color codes for each log level
     */
    private const COLORS = [
        'DEBUG' => "\033[36m",     // Cyan
        'INFO' => "\033[32m",      // Green
        'NOTICE' => "\033[34m",    // Blue
        'WARNING' => "\033[33m",   // Yellow
        'ERROR' => "\033[31m",     // Red
        'CRITICAL' => "\033[35m",  // Magenta
        'ALERT' => "\033[91m",     // Light Red
        'EMERGENCY' => "\033[97;41m", // White on Red background
    ];

    /**
     * ANSI reset code
     */
    private const RESET = "\033[0m";

    /**
     * Dim color for stack trace
     */
    private const DIM = "\033[2m";

    /**
     * Log levels that should include stack traces (300 = WARNING)
     */
    private const TRACE_MIN_LEVEL = 300;

    /**
     * Default format with timestamp, level, and message
     */
    private const DEFAULT_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    private bool $addStackTraces;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = true,
        bool $ignoreEmptyContextAndExtra = true,
        bool $includeStacktrace = true
    ) {
        parent::__construct(
            $format ?? self::DEFAULT_FORMAT,
            $dateFormat,
            $allowInlineLineBreaks,
            $ignoreEmptyContextAndExtra
        );
        $this->addStackTraces = $includeStacktrace;

        // Enable stack traces in parent formatter for exceptions
        if ($includeStacktrace) {
            $this->includeStacktraces(true);
        }
    }

    /**
     * Format the log record with ANSI colors and stack traces for errors
     *
     * @param LogRecord|array $record
     * @return string
     */
    public function format(LogRecord|array $record): string
    {
        // Get level info - handle both Monolog 2.x (array) and 3.x (LogRecord)
        if ($record instanceof LogRecord) {
            $levelName = strtoupper($record->level->name);
            $levelValue = $record->level->value;
            $context = $record->context;
        } else {
            $levelName = $record['level_name'] ?? 'INFO';
            $levelValue = $record['level'] ?? 200;
            $context = $record['context'] ?? [];
        }

        $color = self::COLORS[$levelName] ?? '';
        $output = parent::format($record);

        // Add stack trace for error-level logs if no exception in context
        if ($this->addStackTraces && $levelValue >= self::TRACE_MIN_LEVEL) {
            $hasException = $this->hasException($context);

            if (!$hasException) {
                // Generate stack trace for errors without exceptions
                $trace = $this->generateStackTrace();
                if ($trace !== '') {
                    $output = rtrim($output) . "\n" . self::DIM . $trace . self::RESET . "\n";
                }
            }
        }

        if ($color !== '') {
            return $color . $output . self::RESET;
        }

        return $output;
    }

    /**
     * Check if context contains an exception
     *
     * @param array $context
     * @return bool
     */
    private function hasException(array $context): bool
    {
        foreach ($context as $value) {
            if ($value instanceof \Throwable) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a stack trace string, excluding internal logging calls
     *
     * @return string
     */
    private function generateStackTrace(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        $lines = [];

        // Skip internal Monolog/logging frames
        $skipPrefixes = [
            'Monolog\\',
            'CleatSquad\\LogStream\\Logger\\',
            'Magento\\Framework\\Logger\\',
        ];

        foreach ($trace as $frame) {
            $class = $frame['class'] ?? '';

            // Skip internal logging frames
            $skip = false;
            foreach ($skipPrefixes as $prefix) {
                if (str_starts_with($class, $prefix)) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                continue;
            }

            $file = $frame['file'] ?? 'unknown';
            $line = $frame['line'] ?? 0;
            $function = $frame['function'];

            if ($class !== '') {
                $function = $class . ($frame['type'] ?? '::') . $function;
            }

            // Shorten file path for readability
            $file = $this->shortenPath($file);

            $lines[] = sprintf("  #%d %s:%d %s()", count($lines), $file, $line, $function);

            // Limit stack trace depth
            if (count($lines) >= 8) {
                break;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Shorten file path by removing common prefixes
     *
     * @param string $path
     * @return string
     */
    private function shortenPath(string $path): string
    {
        // Common Magento path prefixes to remove
        $prefixes = [
            '/var/www/html/',
            '/app/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return substr($path, strlen($prefix));
            }
        }

        // If path is too long, show only last 3 parts
        if (strlen($path) > 60) {
            $parts = explode('/', $path);
            if (count($parts) > 3) {
                return '.../' . implode('/', array_slice($parts, -3));
            }
        }

        return $path;
    }
}
