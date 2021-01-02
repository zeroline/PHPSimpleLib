<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

abstract class Middleware
{
    /**
     *
     * @var string
     */
    private $calledMethod = null;
    
    /**
     *
     * @var array
     */
    private $calledMethodArguments = null;
    
    /**
     *
     * @var \PHPSimpleLib\Core\Controlling\Controller
     */
    private $controller = null;
    
    /**
     *
     * @var array
     */
    private $config = array();
    
    /**
     *
     * @param string $method
     * @param array $arguments
     */
    public function setCalledMethod($method, array $arguments = array())
    {
        $this->calledMethod = $method;
        $this->calledMethodArguments = $arguments;
    }
    
    /**
     *
     * @param \PHPSimpleLib\Core\Controlling\Controller $controller
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }
    
    /**
     *
     * @param array $config
     */
    public function setConfig(array $config = array())
    {
        $this->config = $config;
    }
        
    /**
     *
     * @return mixed
     */
    protected function getConfig($key, $fallback = null)
    {
        $value = (array_key_exists($key, $this->config) ? $this->config[$key] : $fallback);
        return $value;
    }
    
    /**
     *
     * @return string
     */
    protected function getCalledMethod()
    {
        return $this->calledMethod;
    }
    
    /**
     *
     * @return array
     */
    protected function getCalledMethodArguments()
    {
        return $this->calledMethodArguments;
    }
    
    /**
     *
     * @return \PHPSimpleLib\Core\Controlling\Controller
     */
    protected function getController()
    {
        return $this->controller;
    }
    
    /**
     *
     */
    abstract public function process();
}
