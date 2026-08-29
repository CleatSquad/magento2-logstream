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

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

/**
 * Class JsonStreamFormatter
 * A JSON formatter optimized for Kubernetes, New Relic, Datadog, and other log aggregators.
 *
 * Features:
 * - Structured JSON output compatible with cloud log aggregators
 * - New Relic APM compatible fields (level, message, timestamp, etc.)
 * - Kubernetes logging best practices (single-line JSON per log entry)
 * - Exception stack traces included as structured data
 * - Request context (URI, method, IP) when available
 * - Consistent field naming for easy parsing
 */
class JsonStreamFormatter extends JsonFormatter
{
    /**
     * Service/application name for log aggregators
     */
    private string $serviceName;

    /**
     * Environment name (production, staging, development)
     */
    private string $environment;

    /**
     * Whether to include stack traces for error-level logs
     */
    private bool $includeStackTrace;

    /**
     * Log levels that should include stack traces (300 = WARNING)
     */
    private const TRACE_MIN_LEVEL = 300;

    public function __construct(
        string $serviceName = 'magento',
        string $environment = 'production',
        bool $includeStackTrace = true,
        int $batchMode = self::BATCH_MODE_JSON,
        bool $appendNewline = true
    ) {
        parent::__construct($batchMode, $appendNewline);
        $this->serviceName = $serviceName;
        $this->environment = $environment;
        $this->includeStackTrace = $includeStackTrace;
        $this->includeStacktraces(true);
    }

    /**
     * Format the log record as structured JSON for log aggregators
     *
     * @param LogRecord|array $record
     * @return string
     */
    public function format(LogRecord|array $record): string
    {
        // Extract data based on Monolog version
        if ($record instanceof LogRecord) {
            $levelName = strtoupper($record->level->name);
            $levelValue = $record->level->value;
            $message = $record->message;
            $context = $record->context;
            $extra = $record->extra;
            $channel = $record->channel;
            $datetime = $record->datetime;
        } else {
            $levelName = $record['level_name'] ?? 'INFO';
            $levelValue = $record['level'] ?? 200;
            $message = $record['message'] ?? '';
            $context = $record['context'] ?? [];
            $extra = $record['extra'] ?? [];
            $channel = $record['channel'] ?? 'app';
            $datetime = $record['datetime'] ?? new \DateTimeImmutable();
        }

        // Build structured log entry
        $data = [
            // Timestamp in ISO 8601 format (required by most log aggregators)
            'timestamp' => $datetime->format(\DateTimeInterface::ATOM),
            // Alternative timestamp formats for compatibility
            '@timestamp' => $datetime->format(\DateTimeInterface::ATOM),
            'time' => $datetime->format('Y-m-d\TH:i:s.uP'),

            // Log level information
            'level' => $levelName,
            'level_name' => $levelName,
            'severity' => $this->mapSeverity($levelValue),
            'log.level' => $levelName,  // New Relic format

            // Message
            'message' => $message,
            'msg' => $message,  // Alternative field for some aggregators

            // Service/application context
            'service' => $this->serviceName,
            'service.name' => $this->serviceName,  // New Relic format
            'application' => $this->serviceName,
            'environment' => $this->environment,
            'env' => $this->environment,

            // Logger channel
            'channel' => $channel,
            'logger' => $channel,
        ];

        // Add request context if available
        $requestData = $this->getRequestContext();
        if (!empty($requestData)) {
            $data['request'] = $requestData;
            // Flatten for New Relic
            foreach ($requestData as $key => $value) {
                $data['http.' . $key] = $value;
            }
        }

        // Add context data (flatten for easier querying)
        if (!empty($context)) {
            // Handle exception specially
            if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
                $exception = $context['exception'];
                $data['error'] = [
                    'class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
                $data['error.class'] = get_class($exception);  // New Relic format
                $data['error.message'] = $exception->getMessage();
                $data['stack_trace'] = $exception->getTraceAsString();
                unset($context['exception']);
            }

            // Add remaining context
            if (!empty($context)) {
                $data['context'] = $context;
                // Flatten simple values for querying
                foreach ($context as $key => $value) {
                    if (is_scalar($value)) {
                        $data['ctx.' . $key] = $value;
                    }
                }
            }
        }

        // Add extra data
        if (!empty($extra)) {
            $data['extra'] = $extra;
        }

        // Add stack trace for error-level logs without exceptions
        if ($this->includeStackTrace && $levelValue >= self::TRACE_MIN_LEVEL) {
            if (!isset($data['stack_trace'])) {
                $trace = $this->generateStackTrace();
                if (!empty($trace)) {
                    $data['stack_trace'] = implode("\n", $trace);
                    $data['trace'] = $trace;  // Array format for structured querying
                }
            }
        }

        // Add trace/span IDs if available (for distributed tracing)
        $traceId = $_SERVER['HTTP_X_TRACE_ID'] ?? $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
        if ($traceId !== null) {
            $data['trace_id'] = $traceId;
            $data['trace.id'] = $traceId;  // New Relic format
        }

        // Kubernetes metadata if available
        $podName = $_SERVER['HOSTNAME'] ?? $_ENV['HOSTNAME'] ?? null;
        if ($podName !== null) {
            $data['kubernetes'] = [
                'pod_name' => $podName,
                'namespace' => $_ENV['POD_NAMESPACE'] ?? 'default',
                'container_name' => $_ENV['CONTAINER_NAME'] ?? $this->serviceName,
            ];
        }

        return $this->toJson($data) . "\n";
    }

    /**
     * Map Monolog level to severity for log aggregators
     *
     * @param int $level
     * @return string
     */
    private function mapSeverity(int $level): string
    {
        return match (true) {
            $level >= 600 => 'EMERGENCY',
            $level >= 550 => 'ALERT',
            $level >= 500 => 'CRITICAL',
            $level >= 400 => 'ERROR',
            $level >= 300 => 'WARNING',
            $level >= 250 => 'NOTICE',
            $level >= 200 => 'INFO',
            default => 'DEBUG',
        };
    }

    /**
     * Get request context from PHP globals
     *
     * @return array
     */
    private function getRequestContext(): array
    {
        if (PHP_SAPI === 'cli') {
            return [
                'type' => 'cli',
                'script' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
            ];
        }

        $data = [];

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $data['method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $data['uri'] = $_SERVER['REQUEST_URI'];
            $data['path'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $data['host'] = $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $data['client_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        // Use X-Forwarded-For if behind proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $data['client_ip'] = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        return $data;
    }

    /**
     * Generate a stack trace array, excluding internal logging calls
     *
     * @return array
     */
    private function generateStackTrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        $lines = [];

        // Skip internal Monolog/logging frames
        $skipPrefixes = [
            'Monolog\\',
            'CleatSquad\\LogStream\\Logger\\',
            'Magento\\Framework\\Logger\\',
        ];

        foreach ($trace as $i => $frame) {
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

            $lines[] = sprintf("#%d %s:%d %s()", count($lines), $file, $line, $function);

            // Limit stack trace depth
            if (count($lines) >= 10) {
                break;
            }
        }

        return $lines;
    }
}
