<?php

declare(strict_types=1);

namespace CleatSquad\LogStream\Test\Unit\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use CleatSquad\LogStream\Model\Config\Source\LogLevel;

/**
 * Class LogLevelTest
 * @covers \CleatSquad\LogStream\Model\Config\Source\LogLevel
 */
class LogLevelTest extends TestCase
{
    /**
     * Test the toOptionArray method.
     *
     * Uses fixed integer values that are the same in both Monolog 2.x and 3.x
     */
    public function testToOptionArray(): void
    {
        $logLevel = new LogLevel();
        $options = $logLevel->toOptionArray();

        // These integer values are the same in both Monolog 2.x and 3.x
        $expectedOptions = [
            ['value' => 100, 'label' => 'DEBUG'],
            ['value' => 200, 'label' => 'INFO'],
            ['value' => 250, 'label' => 'NOTICE'],
            ['value' => 300, 'label' => 'WARNING'],
            ['value' => 400, 'label' => 'ERROR'],
            ['value' => 500, 'label' => 'CRITICAL'],
            ['value' => 550, 'label' => 'ALERT'],
            ['value' => 600, 'label' => 'EMERGENCY'],
        ];

        $this->assertEquals($expectedOptions, $options);
    }
}
