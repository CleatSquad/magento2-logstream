<?php

declare(strict_types=1);

namespace CleatSquad\LogStream\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;
use Monolog\Level;
use Monolog\Logger;

/**
 * Class LogLevel
 * Source model for log level options.
 */
class LogLevel implements OptionSourceInterface
{
   /**
     * Get log levels.
     * returns an array of log levels. Each log level is represented as an associative array
     *
     * @return Phrase[][]|int[][]
     */
    public function toOptionArray(): array
    {
        // Monolog 3.x uses Level enum, Monolog 2.x uses integer constants
        if (class_exists(Level::class)) {
            return [
                ['value' => Level::Debug->value, 'label' => __('DEBUG')],
                ['value' => Level::Info->value, 'label' => __('INFO')],
                ['value' => Level::Notice->value, 'label' => __('NOTICE')],
                ['value' => Level::Warning->value, 'label' => __('WARNING')],
                ['value' => Level::Error->value, 'label' => __('ERROR')],
                ['value' => Level::Critical->value, 'label' => __('CRITICAL')],
                ['value' => Level::Alert->value, 'label' => __('ALERT')],
                ['value' => Level::Emergency->value, 'label' => __('EMERGENCY')],
            ];
        }

        return [
            ['value' => Logger::DEBUG, 'label' => __('DEBUG')],
            ['value' => Logger::INFO, 'label' => __('INFO')],
            ['value' => Logger::NOTICE, 'label' => __('NOTICE')],
            ['value' => Logger::WARNING, 'label' => __('WARNING')],
            ['value' => Logger::ERROR, 'label' => __('ERROR')],
            ['value' => Logger::CRITICAL, 'label' => __('CRITICAL')],
            ['value' => Logger::ALERT, 'label' => __('ALERT')],
            ['value' => Logger::EMERGENCY, 'label' => __('EMERGENCY')],
        ];
    }
}
