<?php

declare(strict_types=1);

namespace CleatSquad\LogStream\Test\Unit\Logger\Formatter;

use CleatSquad\LogStream\Logger\Formatter\ColoredLineFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * Class ColoredLineFormatterTest
 * @covers \CleatSquad\LogStream\Logger\Formatter\ColoredLineFormatter
 */
class ColoredLineFormatterTest extends TestCase
{
    private ColoredLineFormatter $formatter;

    protected function setUp(): void
    {
        // Disable stack traces for basic color tests
        $this->formatter = new ColoredLineFormatter(
            includeStacktrace: false
        );
    }

    public function testFormatterIsInstanceOfLineFormatter(): void
    {
        $this->assertInstanceOf(ColoredLineFormatter::class, $this->formatter);
    }

    public function testFormatAddsGreenColorForInfoLevel(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        // Check that output contains green ANSI code
        $this->assertStringContainsString("\033[32m", $output);
        // Check that output contains reset code
        $this->assertStringContainsString("\033[0m", $output);
        // Check that output contains the message
        $this->assertStringContainsString('Test message', $output);
    }

    public function testFormatAddsRedColorForErrorLevel(): void
    {
        $record = $this->createLogRecord('ERROR', 'Error message');
        $output = $this->formatter->format($record);

        // Check that output contains red ANSI code
        $this->assertStringContainsString("\033[31m", $output);
        $this->assertStringContainsString('Error message', $output);
    }

    public function testFormatAddsYellowColorForWarningLevel(): void
    {
        $record = $this->createLogRecord('WARNING', 'Warning message');
        $output = $this->formatter->format($record);

        // Check that output contains yellow ANSI code
        $this->assertStringContainsString("\033[33m", $output);
        $this->assertStringContainsString('Warning message', $output);
    }

    public function testFormatAddsCyanColorForDebugLevel(): void
    {
        $record = $this->createLogRecord('DEBUG', 'Debug message');
        $output = $this->formatter->format($record);

        // Check that output contains cyan ANSI code
        $this->assertStringContainsString("\033[36m", $output);
        $this->assertStringContainsString('Debug message', $output);
    }

    public function testFormatAddsMagentaColorForCriticalLevel(): void
    {
        $record = $this->createLogRecord('CRITICAL', 'Critical message');
        $output = $this->formatter->format($record);

        // Check that output contains magenta ANSI code
        $this->assertStringContainsString("\033[35m", $output);
        $this->assertStringContainsString('Critical message', $output);
    }

    public function testStackTraceIncludedForErrorLevelWhenEnabled(): void
    {
        $formatter = new ColoredLineFormatter(includeStacktrace: true);
        $record = $this->createLogRecord('ERROR', 'Error with trace');
        $output = $formatter->format($record);

        // Check that output contains message
        $this->assertStringContainsString('Error with trace', $output);
        // Stack trace should include dim color code
        $this->assertStringContainsString("\033[2m", $output);
    }

    public function testNoStackTraceForInfoLevel(): void
    {
        $formatter = new ColoredLineFormatter(includeStacktrace: true);
        $record = $this->createLogRecord('INFO', 'Info message');
        $output = $formatter->format($record);

        // Check that output contains message
        $this->assertStringContainsString('Info message', $output);
        // No stack trace (dim color code) for INFO level
        $this->assertStringNotContainsString("\033[2m", $output);
    }

    /**
     * Create a log record compatible with both Monolog 2.x and 3.x
     *
     * @param string $levelName
     * @param string $message
     * @return LogRecord|array
     */
    private function createLogRecord(string $levelName, string $message): LogRecord|array
    {
        // Monolog 3.x uses LogRecord class
        if (class_exists(LogRecord::class)) {
            $level = Level::fromName($levelName);
            return new LogRecord(
                datetime: new \DateTimeImmutable(),
                channel: 'test',
                level: $level,
                message: $message,
                context: [],
                extra: []
            );
        }

        // Monolog 2.x uses array
        $levelMap = [
            'DEBUG' => 100,
            'INFO' => 200,
            'NOTICE' => 250,
            'WARNING' => 300,
            'ERROR' => 400,
            'CRITICAL' => 500,
            'ALERT' => 550,
            'EMERGENCY' => 600,
        ];

        return [
            'message' => $message,
            'context' => [],
            'level' => $levelMap[$levelName] ?? 200,
            'level_name' => $levelName,
            'channel' => 'test',
            'datetime' => new \DateTimeImmutable(),
            'extra' => [],
        ];
    }
}
