<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\ObjectFactory;

use PHPSimpleLib\Core\ObjectFactory\ObjectFactory as ObjectFactory;

trait Singleton
{
    public static $instances = array();

    public static function getInstance()
    {
        $c = get_called_class();
        if (!array_key_exists($c, static::$instances)) {
            static::$instances[$c] = ObjectFactory::create($c, func_get_args());
        }
        return static::$instances[$c];
    }
    private function __clone()
    {
    }
    public function __wakeup()
    {
    }
}
