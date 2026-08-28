<?php

/**
 * Copyright (c) 2024 Mohamed EL Mrabet
 * CleatSquad - https://cleatsquad.dev
 *
 * This file is part of the CleatSquad_LogStream module.
 * Licensed under the MIT License. See the LICENSE file in the module root.
 */

namespace CleatSquad\LogStream\Test\Unit\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use CleatSquad\LogStream\Logger\StdoutHandler;

/**
 * Class StdoutHandlerTest
 * @covers CleatSquad\LogStream\Logger\StdoutHandler
 */
class StdoutHandlerTest extends TestCase
{
    /**
     * Minimum log level (DEBUG = 100)
     */
    private const MIN_LEVEL = 100;

    private function createStdoutHandler(): StdoutHandler
    {
        $formatter = new JsonFormatter();
        return new StdoutHandler($formatter);
    }

    /**
     * Get the integer value from a log level (handles both Monolog 2.x and 3.x)
     *
     * @param int|Level $level
     * @return int
     */
    private function getLevelValue(int|Level $level): int
    {
        if ($level instanceof Level) {
            return $level->value;
        }
        return $level;
    }

    /**
     * Create a log record for testing (compatible with Monolog 2.x and 3.x)
     *
     * @param int $level
     * @return LogRecord|array
     */
    private function createLogRecord(int $level): LogRecord|array
    {
        if (class_exists(LogRecord::class)) {
            return new LogRecord(
                datetime: new \DateTimeImmutable(),
                channel: 'test',
                level: Level::from($level),
                message: 'Test message',
                context: [],
                extra: []
            );
        }

        return [
            'message' => 'Test message',
            'context' => [],
            'level' => $level,
            'level_name' => 'TEST',
            'channel' => 'test',
            'datetime' => new \DateTimeImmutable(),
            'extra' => [],
        ];
    }

    public function testIsInstanceOfStreamHandler(): void
    {
        $this->assertInstanceOf(StdoutHandler::class, $this->createStdoutHandler());
    }

    public function testGetUrl(): void
    {
        $this->assertEquals('php://stdout', $this->createStdoutHandler()->getUrl());
    }

    public function testGetLevel(): void
    {
        $handler = $this->createStdoutHandler();
        $this->assertEquals(self::MIN_LEVEL, $this->getLevelValue($handler->getLevel()));
    }

    public function testGetBubble(): void
    {
        $this->assertFalse($this->createStdoutHandler()->getBubble());
    }

    public function testGetFormatter(): void
    {
        $this->assertInstanceOf(JsonFormatter::class, $this->createStdoutHandler()->getFormatter());
    }

    public function testIsHandlingDebugLevel(): void
    {
        $handler = $this->createStdoutHandler();
        $record = $this->createLogRecord(100); // DEBUG
        $this->assertTrue($handler->isHandling($record));
    }

    public function testIsHandlingInfoLevel(): void
    {
        $handler = $this->createStdoutHandler();
        $record = $this->createLogRecord(200); // INFO
        $this->assertTrue($handler->isHandling($record));
    }

    public function testIsNotHandlingWarningLevel(): void
    {
        $handler = $this->createStdoutHandler();
        $record = $this->createLogRecord(300); // WARNING
        $this->assertFalse($handler->isHandling($record));
    }

    public function testIsNotHandlingErrorLevel(): void
    {
        $handler = $this->createStdoutHandler();
        $record = $this->createLogRecord(400); // ERROR
        $this->assertFalse($handler->isHandling($record));
    }

    public function testConfiguredLevelRaisesTheThreshold(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('200'); // raise floor to INFO

        $handler = new StdoutHandler(new JsonFormatter(), $scopeConfig);

        $this->assertFalse($handler->isHandling($this->createLogRecord(100))); // DEBUG now excluded
        $this->assertTrue($handler->isHandling($this->createLogRecord(200))); // INFO still included
    }

    public function testConfiguredLevelCannotWidenBeyondTheHandlerRange(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('100'); // below the handler's own baseline

        $handler = new StdoutHandler(new JsonFormatter(), $scopeConfig);

        $this->assertTrue($handler->isHandling($this->createLogRecord(100))); // DEBUG still included
        $this->assertFalse($handler->isHandling($this->createLogRecord(300))); // still capped at INFO
    }

    public function testScopeConfigFailureFallsBackToTheHandlerBaseline(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willThrowException(new \RuntimeException('no DB connection yet'));

        $handler = new StdoutHandler(new JsonFormatter(), $scopeConfig);

        $this->assertTrue($handler->isHandling($this->createLogRecord(100))); // DEBUG still included
    }
}
