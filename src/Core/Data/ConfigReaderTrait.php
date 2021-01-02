<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Helper\ArrayReader;

trait ConfigReaderTrait
{
    /**
    *
    * @var array
    */
    protected $config = array();

    /**
    *
    * @param string $key
    * @param mixed $fallback
    * @return mixed
    */
    protected function getConfig($key, $fallback = null)
    {
           return ArrayReader::getConfig($this->config, $key, $fallback);
    }
}
