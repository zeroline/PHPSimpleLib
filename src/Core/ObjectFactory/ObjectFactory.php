<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\ObjectFactory;

use PHPSimpleLib\Core\ObjectFactory\AnnotationParser as AnnotationParser;

class ObjectFactory
{
    /**
     * Store singleton instances
     *
     * @var array
     */
    private static $instances = array();
/**
     *
     * @param string $class
     * @param boolean $enableAnnotations
     * @return $class
     */
    public static function create($class, array $args = array(), $enableAnnotations = true)
    {
            $reflector = new \ReflectionClass($class);
        $instance = $reflector->newInstanceArgs($args);
        if ($enableAnnotations) {
            AnnotationParser::injectClassesAndComponentsIntoObject($instance);
        }
            return $instance;
    }

    /**
     *
     * @param string $class
     * @param boolean $enableAnnotations
     * @return $class
     */
    public static function singleton($class, array $args = array(), $enableAnnotations = true)
    {
        if (in_array('PHPSimpleLib\\Core\\ObjectFactory\\Singleton', class_uses($class))) {
            return $class::getInstance();
        } elseif (!array_key_exists($class, static::$instances)) {
            static::$instances[$class] = static::create($class, $args, $enableAnnotations);
        }

            return static::$instances[$class];
    }
}
