<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Logging;

use PHPSimpleLib\Core\Logging\EnumLogLevel;
use PHPSimpleLib\Core\Logging\AbstractLogger;

/**
 * Logger class for logging messages to the console.
 */
final class ConsoleLogger extends AbstractLogger
{
    /**
     * Config key for indicating another date time config value
     */
    public const CONFIG_KEY_DATETIME_FORMAT = "dateTimeFormat";

    /**
     * The default date time format used in the log message
     */
    public const DEFAULT_DATE_TIME_FORMAT = \DateTime::ATOM;

    /**
     * Logs the message after interpolation to the console using
     * echo and flush.
     *
     * @param string $level {@see EnumLogLevel}
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $level, string $message, array $context = array()) : void
    {
        $baseMessageString = $this->interpolate($message, $context);
        $dateTime = date($this->getConfig(self::CONFIG_KEY_DATETIME_FORMAT, self::DEFAULT_DATE_TIME_FORMAT));
        $finalMessageString = $dateTime . ' [' . $level . '] ' . $baseMessageString . PHP_EOL;
        echo $finalMessageString;
        flush();
    }

    public function clearing() : void {
        // Do nothing
    }
}
