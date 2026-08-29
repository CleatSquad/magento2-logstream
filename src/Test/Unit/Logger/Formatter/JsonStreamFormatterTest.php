<?php

declare(strict_types=1);

namespace CleatSquad\LogStream\Test\Unit\Logger\Formatter;

use CleatSquad\LogStream\Logger\Formatter\JsonStreamFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonStreamFormatterTest
 * @covers \CleatSquad\LogStream\Logger\Formatter\JsonStreamFormatter
 */
class JsonStreamFormatterTest extends TestCase
{
    private JsonStreamFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JsonStreamFormatter(
            serviceName: 'magento-test',
            environment: 'testing',
            includeStackTrace: false
        );
    }

    public function testFormatterIsInstanceOfJsonFormatter(): void
    {
        $this->assertInstanceOf(JsonStreamFormatter::class, $this->formatter);
    }

    public function testFormatReturnsValidJson(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded, 'Output should be valid JSON');
        $this->assertIsArray($decoded);
    }

    public function testFormatIncludesTimestamp(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('timestamp', $decoded);
        $this->assertArrayHasKey('@timestamp', $decoded);
        $this->assertArrayHasKey('time', $decoded);
    }

    public function testFormatIncludesLogLevel(): void
    {
        $record = $this->createLogRecord('ERROR', 'Error message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('level', $decoded);
        $this->assertEquals('ERROR', $decoded['level']);
        $this->assertArrayHasKey('severity', $decoded);
        $this->assertEquals('ERROR', $decoded['severity']);
    }

    public function testFormatIncludesMessage(): void
    {
        $record = $this->createLogRecord('INFO', 'Test log message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertEquals('Test log message', $decoded['message']);
        // Also check alternative field
        $this->assertArrayHasKey('msg', $decoded);
        $this->assertEquals('Test log message', $decoded['msg']);
    }

    public function testFormatIncludesServiceName(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('service', $decoded);
        $this->assertEquals('magento-test', $decoded['service']);
        // New Relic format
        $this->assertArrayHasKey('service.name', $decoded);
        $this->assertEquals('magento-test', $decoded['service.name']);
    }

    public function testFormatIncludesEnvironment(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('environment', $decoded);
        $this->assertEquals('testing', $decoded['environment']);
        $this->assertArrayHasKey('env', $decoded);
        $this->assertEquals('testing', $decoded['env']);
    }

    public function testFormatIncludesChannel(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('channel', $decoded);
        $this->assertArrayHasKey('logger', $decoded);
    }

    public function testFormatIncludesNewRelicFields(): void
    {
        $record = $this->createLogRecord('WARNING', 'Warning message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        // New Relic specific fields
        $this->assertArrayHasKey('log.level', $decoded);
        $this->assertEquals('WARNING', $decoded['log.level']);
    }

    public function testFormatIncludesContextData(): void
    {
        $record = $this->createLogRecordWithContext('INFO', 'Order created', [
            'order_id' => 12345,
            'customer_email' => 'test@example.com',
        ]);
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('context', $decoded);
        $this->assertEquals(12345, $decoded['context']['order_id']);
        // Flattened context for easier querying
        $this->assertArrayHasKey('ctx.order_id', $decoded);
        $this->assertEquals(12345, $decoded['ctx.order_id']);
    }

    public function testFormatHandlesException(): void
    {
        $exception = new \RuntimeException('Test exception', 500);
        $record = $this->createLogRecordWithContext('ERROR', 'An error occurred', [
            'exception' => $exception,
        ]);
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertEquals('RuntimeException', $decoded['error']['class']);
        $this->assertEquals('Test exception', $decoded['error']['message']);
        $this->assertEquals(500, $decoded['error']['code']);
        // New Relic error fields
        $this->assertArrayHasKey('error.class', $decoded);
        $this->assertArrayHasKey('error.message', $decoded);
    }

    public function testFormatIncludesStackTraceForErrorLevel(): void
    {
        $formatter = new JsonStreamFormatter(
            serviceName: 'test',
            environment: 'test',
            includeStackTrace: true
        );

        $record = $this->createLogRecord('ERROR', 'Error with trace');
        $output = $formatter->format($record);

        $decoded = json_decode($output, true);
        // Stack trace should be included for ERROR level
        $this->assertArrayHasKey('stack_trace', $decoded);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertIsArray($decoded['trace']);
    }

    public function testFormatNoStackTraceForInfoLevel(): void
    {
        $formatter = new JsonStreamFormatter(
            serviceName: 'test',
            environment: 'test',
            includeStackTrace: true
        );

        $record = $this->createLogRecord('INFO', 'Info message');
        $output = $formatter->format($record);

        $decoded = json_decode($output, true);
        // No stack trace for INFO level
        $this->assertArrayNotHasKey('stack_trace', $decoded);
    }

    public function testSeverityMappingDebug(): void
    {
        $record = $this->createLogRecord('DEBUG', 'Debug message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertEquals('DEBUG', $decoded['severity']);
    }

    public function testSeverityMappingCritical(): void
    {
        $record = $this->createLogRecord('CRITICAL', 'Critical message');
        $output = $this->formatter->format($record);

        $decoded = json_decode($output, true);
        $this->assertEquals('CRITICAL', $decoded['severity']);
    }

    public function testOutputEndsWithNewline(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message');
        $output = $this->formatter->format($record);

        $this->assertStringEndsWith("\n", $output);
    }

    public function testOutputIsSingleLine(): void
    {
        $record = $this->createLogRecord('INFO', 'Test message with\nnewlines');
        $output = $this->formatter->format($record);

        // Should only have the trailing newline, no internal newlines in JSON
        $lines = explode("\n", trim($output));
        $this->assertCount(1, $lines, 'JSON output should be single-line');
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
        return $this->createLogRecordWithContext($levelName, $message, []);
    }

    /**
     * Create a log record with context
     *
     * @param string $levelName
     * @param string $message
     * @param array $context
     * @return LogRecord|array
     */
    private function createLogRecordWithContext(string $levelName, string $message, array $context): LogRecord|array
    {
        // Monolog 3.x uses LogRecord class
        if (class_exists(LogRecord::class)) {
            $level = Level::fromName($levelName);
            return new LogRecord(
                datetime: new \DateTimeImmutable(),
                channel: 'test',
                level: $level,
                message: $message,
                context: $context,
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
            'context' => $context,
            'level' => $levelMap[$levelName] ?? 200,
            'level_name' => $levelName,
            'channel' => 'test',
            'datetime' => new \DateTimeImmutable(),
            'extra' => [],
        ];
    }
}
