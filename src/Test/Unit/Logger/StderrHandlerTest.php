<?php

/**
 * Copyright (c) 2025 Mohamed EL Mrabet
 * CleatSquad - https://cleatsquad.dev
 *
 * This file is part of the CleatSquad_LogStream module.
 * Licensed under the MIT License. See the LICENSE file in the module root.
 */

namespace CleatSquad\LogStream\Test\Unit\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use CleatSquad\LogStream\Logger\StderrHandler;

/**
 * Class StderrHandlerTest
 * @covers CleatSquad\LogStream\Logger\StderrHandler
 */
class StderrHandlerTest extends TestCase
{
    /**
     * Minimum log level (WARNING = 300)
     */
    private const MIN_LEVEL = 300;

    private function createStderrHandler(): StderrHandler
    {
        $formatter = new JsonFormatter();
        return new StderrHandler($formatter);
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
        $this->assertInstanceOf(StderrHandler::class, $this->createStderrHandler());
    }

    public function testGetUrl(): void
    {
        $this->assertEquals('php://stderr', $this->createStderrHandler()->getUrl());
    }

    public function testGetLevel(): void
    {
        $handler = $this->createStderrHandler();
        $this->assertEquals(self::MIN_LEVEL, $this->getLevelValue($handler->getLevel()));
    }

    public function testGetBubble(): void
    {
        $this->assertFalse($this->createStderrHandler()->getBubble());
    }

    public function testGetFormatter(): void
    {
        $this->assertInstanceOf(JsonFormatter::class, $this->createStderrHandler()->getFormatter());
    }

    public function testIsNotHandlingDebugLevel(): void
    {
        $handler = $this->createStderrHandler();
        $record = $this->createLogRecord(100); // DEBUG
        $this->assertFalse($handler->isHandling($record));
    }

    public function testIsNotHandlingInfoLevel(): void
    {
        $handler = $this->createStderrHandler();
        $record = $this->createLogRecord(200); // INFO
        $this->assertFalse($handler->isHandling($record));
    }

    public function testIsHandlingWarningLevel(): void
    {
        $handler = $this->createStderrHandler();
        $record = $this->createLogRecord(300); // WARNING
        $this->assertTrue($handler->isHandling($record));
    }

    public function testIsHandlingErrorLevel(): void
    {
        $handler = $this->createStderrHandler();
        $record = $this->createLogRecord(400); // ERROR
        $this->assertTrue($handler->isHandling($record));
    }

    public function testIsHandlingEmergencyLevel(): void
    {
        $handler = $this->createStderrHandler();
        $record = $this->createLogRecord(600); // EMERGENCY
        $this->assertTrue($handler->isHandling($record));
    }
}
