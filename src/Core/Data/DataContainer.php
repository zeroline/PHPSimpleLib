<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

class DataContainer
{
    
    /**
     *
     * @var array
     */
    private $data = array();
    
    /**
     *
     * @param array|object $data
     */
    public function __construct($data = array())
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        $this->data = $data;
    }
    
    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return stored data if found else
     * return given fallback value
     *
     * @param string $name
     * @param mixed $fallback
     * @return mixed|null
     */
    public function get(string $name, $fallback = null)
    {
        if (is_null($this->{$name})) {
            return $fallback;
        }
        return $this->{$name};
    }
    
    /**
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }
    
    /**
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    /**
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getData();
    }
}
