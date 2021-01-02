<?php

namespace PHPSimpleLib\Modules\JobManagement\Lib;

final class EnumJobHandlingResult
{
    /**
     * Indicates that everything is fine
     */
    public const SUCCESS = 1;

    /**
     * Something went wrong but may work next time
     */
    public const FAILED = 10;

    /**
     * Something did terribly go wrong, don't try again
     */
    public const ERROR = 100;
}
