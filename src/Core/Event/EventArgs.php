<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Event;

class EventArgs
{
    private $arguments = array();

    public function __construct(array $arguments = array())
    {
        $this->arguments = $arguments;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    public static function empty() : EventArgs
    {
        return new EventArgs();
    }
}
