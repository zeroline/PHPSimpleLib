<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\ObjectFactory;

use PHPSimpleLib\Core\ObjectFactory\ObjectFactory as ObjectFactory;

trait Instanciator
{
    public static function getInstance()
    {
        return ObjectFactory::create(get_called_class(), func_get_args());
    }
    final private function __clone()
    {
    }
    final private function __wakeup()
    {
    }
}
