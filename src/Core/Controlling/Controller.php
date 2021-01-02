<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Middleware as Middleware;

class Controller
{
    use \PHPSimpleLib\Core\ObjectFactory\Singleton;


    const METHOD_SUFFIX = 'Action';
    const CONTROLLER_SUFFIX = 'Controller';
/**
     *
     * @Inject \PHPSimpleLib\Core\Event\Mediator
     * @var \PHPSimpleLib\Core\Event\Mediator
     */
    protected $mediator = null;
/**
     *
     * @var array
     */
    protected $routeMapping = array();
/**
     *
     * @var array
     */
    protected $middleware = array();
/**
     *
     * @var array
     */
    protected $middlewareData = array();
/**
     * @var string
     */
    protected $methodToCall = null;
/**
     *
     * @param string $fullClassName
     * @param array $config
     */
    protected function addMiddleware(string $fullClassName, array $config = array())
    {
        $this->middleware[$fullClassName] = $config;
    }

    /**
     *
     * @return array
     */
    private function getMiddlewares(): array
    {
        return $this->middleware;
    }

    /**
     *
     * @param string $route
     * @param string $actionToBeCalled
     */
    protected function addRouteMapping(string $route, string $actionToBeCalled)
    {
        $this->routeMapping[$route] = (object)array(
            'moduleName' => (explode('\\', get_class($this))[1]),
            'controllerClass' => get_class($this),
            'controllerInstance' => $this,
            'actionName' => $actionToBeCalled,
            'route' => $route
        );
    }

    /**
     * Returns the simplified class name
     *
     * @return string
     */
    public function getSimplifiedControllerName(): string
    {
        return (str_replace(self::CONTROLLER_SUFFIX, '', substr(get_called_class(), strrpos(get_called_class(), '\\') + 1)));
    }
    /**
     *
     * @return array
     */
    public function getRouteMappings(): array
    {
        return $this->routeMapping;
    }

    /**
     *
     * @param string $methodName
     * @return boolean
     */
    public function isActionAvailable(string $methodName): bool
    {
        return (bool)method_exists($this, $methodName . self::METHOD_SUFFIX);
    }

    /**
     *
     * @param string $middlewareClass
     * @param string $fieldName
     * @param mixed $value
     */
    public function injectMiddlewareField(string $middlewareClass, string $fieldName, $value)
    {
        if (!array_key_exists($middlewareClass, $this->middlewareData)) {
            $this->middlewareData[$middlewareClass] = array();
        }
        $this->middlewareData[$middlewareClass][$fieldName] = $value;
    }

    /**
     *
     * @param string $middlewareClass
     * @param string $fieldName
     * @param mixed $fallback
     * @return mixed
     */
    public function getInjectedMiddlewareField(string $middlewareClass, string $fieldName, $fallback = null)
    {
        if (array_key_exists($middlewareClass, $this->middlewareData)) {
            if (array_key_exists($fieldName, $this->middlewareData[$middlewareClass])) {
                return $this->middlewareData[$middlewareClass][$fieldName];
            }
        }
        return $fallback;
    }

    /**
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name . self::METHOD_SUFFIX)) {
            $this->methodToCall = $name;
// Load and execute middlewares
            foreach ($this->getMiddlewares() as $middlewareClass => $config) {
                $middleware = new $middlewareClass();
                if ($middleware instanceof Middleware) {
                    $middleware->setConfig($config);
                    $middleware->setController($this);
                    $middleware->setCalledMethod($name, (is_array($arguments) ? $arguments : array()));
                    if (($result = $middleware->process()) !== true) {
                        return $result;
                    }
                } else {
                    throw new \Exception("Invalid middleware class type.", 500);
                }
            }
            return call_user_func_array(array($this, $name . self::METHOD_SUFFIX), $arguments);
        }
        return call_user_func_array(array($this, $name), $arguments);
    }
}
