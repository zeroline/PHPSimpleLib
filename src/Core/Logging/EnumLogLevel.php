<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Logging;

/**
 * Enumeration for all log levels.
 * Example taken from PSR-3 standards.
 * @see https://www.php-fig.org/psr/psr-3/
 */
final class EnumLogLevel
{
    /**
     * System is unusable.
     */
    public const EMERGENCY = 'emergency';
/**
     * Action must be taken immediately.
     */
    public const ALERT     = 'alert';
/**
     * Critical conditions.
     */
    public const CRITICAL  = 'critical';
/**
     * Runtime errors that do not require immediate action but should typically
     * be logged.
     */
    public const ERROR     = 'error';
/**
     * Exceptional occurrences that are not errors.
     */
    public const WARNING   = 'warning';
/**
     * Normal but significant events.
     */
    public const NOTICE    = 'notice';
/**
     * Interesting events.
     */
    public const INFO      = 'info';
/**
     * Detailed debug information.
     */
    public const DEBUG     = 'debug';
}
