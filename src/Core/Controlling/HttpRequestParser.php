<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Parser;
use PHPSimpleLib\Core\Controlling\ModuleManager;

class HttpRequestParser extends Parser
{
    protected $routeMappings = array();
    
    /**
     *
     * @var string
     */
    private $module = null;

    /**
     *
     * @var Controller
     */
    private $controller = null;

    /**
     *
     * @var string
     */
    private $action = null;

    /**
     *
     * @var array
     */
    private $parameter = array();
    
    public function setRouteMappings(array $mappings)
    {
        $this->routeMappings = $mappings;
    }

    public function getParsedAction(): ?string
    {
        return $this->action;
    }

    public function getParsedController(): ?Controller
    {
        return $this->controller;
    }

    public function getParsedModule(): ?string
    {
        return $this->module;
    }

    public function getParsedParameter(): array
    {
        return $this->parameter;
    }
    
    public function getPostedParameter(): array
    {
        if (is_array($_POST)) {
            return $_POST;
        }
        return array();
    }
    
    public function getPostedBody(): string
    {
        return file_get_contents('php://input');
    }

    public function parse()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $combinedPath = $method . ':' . $path;
        
        if (array_key_exists($combinedPath, $this->routeMappings)) {
            $foundRoute = $this->routeMappings[$combinedPath];
            $this->action = $foundRoute->actionName;
            $this->controller = $foundRoute->controllerInstance;
            $this->module = $foundRoute->moduleName;
            return true;
        } else {
            $matches = array();
            foreach ($this->routeMappings as $mapping => $routeData) {
                //if (preg_match('~' . $mapping . '~', $combinedPath, $matches)) {
                if (preg_match('#^' . $mapping . '$#', $combinedPath, $matches)) {
                    $foundRoute = $this->routeMappings[$mapping];
                    $this->action = $foundRoute->actionName;
                    $this->controller = $foundRoute->controllerInstance;
                    $this->module = $foundRoute->moduleName;
                    array_shift($matches);
                    $this->parameter = $matches;
                    return true;
                }
            }
        }
        return false;
    }
}
