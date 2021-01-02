<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Logging;

use PHPSimpleLib\Core\Logging\EnumLogLevel;
use PHPSimpleLib\Core\Data\ConfigReaderTrait;
use PHPSimpleLib\Core\ObjectFactory\Singleton;

/**
 * Abstract logger class for implementing real logger classes
 * Base code from PSR-3 standard.
 * @see https://github.com/php-fig/log/blob/master/Psr/Log/LoggerTrait.php
 *
 * Code has been alterted to match PHP >= 7.1 and this frameworks standards.
 */
abstract class AbstractLogger
{
    use ConfigReaderTrait;
                                                                                                                                                                                                                                                                                                        use Singleton;


    private const INTERPOLATE_VAR_PREFIX = "{";
    private const INTERPOLATE_VAR_SUFFIX = "}";
    private const INTERPOLATE_OBJECT_TOSTRING_METHOD_NAME = "__toString";
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug(string $message, array $context = array()): void
    {
        $this->log(EnumLogLevel::DEBUG, $message, $context);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate(string $message, array $context = array()): string
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
        // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, self::INTERPOLATE_OBJECT_TOSTRING_METHOD_NAME))) {
                $replace[self::INTERPOLATE_VAR_PREFIX . $key . self::INTERPOLATE_VAR_SUFFIX] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    abstract public function log(string $level, string $message, array $context = array()): void;
/**
     * Can be used to clean old log data, files or rotate them
     *
     * @return void
     */
    abstract public function clearing(): void;
}
