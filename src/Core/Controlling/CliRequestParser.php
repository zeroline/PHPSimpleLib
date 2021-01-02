<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Parser;
use PHPSimpleLib\Core\Controlling\ModuleManager;

class CliRequestParser extends Parser
{
    private const DEFAULT_ACTION = 'help';
    protected $routeMappings = array();
    private $module = null;
    private $controller = null;
    private $action = null;
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

    private function parseArgs($argv = null)
    {
        array_shift($argv);
        $out = array();
        for ($i = 0, $j = count($argv); $i < $j; $i++) {
            $arg = $argv[$i];
// --foo --bar=baz
            if (substr($arg, 0, 2) === '--') {
                $eqPos  = strpos($arg, '=');
// --foo
                if ($eqPos === false) {
                    $key = substr($arg, 2);
// --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value  = $argv[$i + 1];
                        $i++;
                    } else {
                        $value  = isset($out[$key]) ? $out[$key] : true;
                    }
                    $out[$key]  = $value;
                } else {
                    $key = substr($arg, 2, $eqPos - 2);
                    $value  = substr($arg, $eqPos + 1);
                    $out[$key]  = $value;
                }
            } elseif (substr($arg, 0, 1) === '-') {
            // -k=value
                if (substr($arg, 2, 1) === '=') {
                    $key = substr($arg, 1, 1);
                    $value  = substr($arg, 3);
                    $out[$key]  = $value;
                } else {
                    $chars  = str_split(substr($arg, 1));
                    foreach ($chars as $char) {
                            $key = $char;
                            $value  = isset($out[$key]) ? $out[$key] : true;
                            $out[$key]  = $value;
                    }
                    // -a value1 -abc value2
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $out[$key]  = $argv[$i + 1];
                        $i++;
                    }
                }
            } else {
                $value  = $arg;
                $out[]  = $value;
            }
        }
        return $out;
    }

    public function parse()
    {
        $argv = null;
        if (defined('ARGV')) {
            $argv = ARGV;
        } else {
            $argv = array();
        }
        $arguments = $this->parseArgs($argv);
        $funcParameter = array_slice($arguments, 3);
        $funcParameterValues = array_values($funcParameter);
        $argumentCount = count($arguments);
// Reset
        $moduleName = null;
        $this->module = $moduleName;
        $controllerName = null;
        $this->controller = null;
        $actionName = self::DEFAULT_ACTION;
        $this->action = self::DEFAULT_ACTION;
        $this->parameter = array();
// No arguments found, no specific module selected
        //
        if ($argumentCount == 0) {
            $this->setNotFound();
            return false;
        }

        $mm = ModuleManager::getInstance();
// One argument found, specific module selected
        if ($argumentCount >= 1) {
            $moduleName = $arguments[0];
            $this->module = trim(ucfirst($moduleName));
            $this->setPartiallyFound();
        }

        // Two arguments found, specific module and controller selected
        if ($argumentCount >= 2) {
            $controllerName = $arguments[1];
            $this->controller = $mm->getCommandControllerByModuleAndClass(strtolower($moduleName), strtolower($controllerName));
            ;
            $this->setPartiallyFound();
        }

        if ($argumentCount >= 3) {
            $actionName = $arguments[2];
            if ($this->controller && $this->controller->isActionAvailable($actionName)) {
                $this->action = $actionName;
                $this->parameter = $funcParameter;
                $this->setFound();
            }
        }
    }
}
