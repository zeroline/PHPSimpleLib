<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\ObjectFactory;

//use PHPSimpleLib\Core\ObjectFactory as ObjectFactory;

final class AnnotationParser
{
    const INJECTION_PARAMETER = 'Inject';
    const INJECTION_PARAMETER_SINGLETON = 'Singleton';
    const INJECTION_PARAMETER_INSTANCE = 'Instance';
    const INJECTION_PARAMETER_OPTIONS = 'Options';
        
    /**
     *
     * @param string $comment
     * @return arra
     */
    private static function resolveParamterFromDocComment($comment)
    {
        $keyPattern = "[A-z0-9\_\-]+";
        $endPattern = "[ ]*(?:@|\r\n|\n)";
        $pattern = "/@(?=(.*)" . $endPattern . ")/U";
        $foundParameter = array();

        preg_match_all($pattern, $comment, $matches);
        foreach ($matches[1] as $rowMatch) {
            if (preg_match("/^(" . $keyPattern . ") (.*)$/", $rowMatch, $match)) {
                $parsedValue = isset($match[2]) ? $match[2] : null;
                if (isset($foundParameter[$match[1]])) {
                    $foundParameter[$match[1]] = array_merge((array)$foundParameter[$match[1]], (array)$parsedValue);
                } else {
                    $foundParameter[$match[1]] = $parsedValue;
                }
            } elseif (preg_match("/^" . $keyPattern . "$/", $rowMatch, $match)) {
                $foundParameter[$rowMatch] = true;
            } else {
                $foundParameter[$rowMatch] = null;
            }
        }
        return $foundParameter;
    }
    
    /**
     *
     * @param string $name
     * @param string $comment
     * @return boolean
     */
    private static function hasParameter($name, $comment)
    {
        $paramter = static::resolveParamterFromDocComment($comment);
        return array_key_exists($name, $paramter);
    }

    /**
     *
     * @param string $paramter
     * @param string $comment
     * @return string|null
     */
    private static function resolveParamterValueFromDocComment($paramter, $comment)
    {
        if (static::hasParameter($paramter, $comment)) {
            return static::resolveParamterFromDocComment($comment)[$paramter];
        }
        return null;
    }

    /**
     *
     * @param \ReflectionObject $ref
     * @return array
     */
    private static function getClassProperties($ref)
    {
        $props = $ref->getProperties();
        $props_arr = array();
        foreach ($props as $prop) {
            $f = $prop->getName();
            //$props_arr[$f] = $prop;
            $props_arr[] = $prop;
        }
        if (($parentClass = $ref->getParentClass())) {
            $parent_props_arr = static::getClassProperties(new \ReflectionClass($parentClass->getName()));//RECURSION
            if (count($parent_props_arr) > 0) {
                $props_arr = array_merge($parent_props_arr, $props_arr);
            }
        }
        return $props_arr;
    }

    /**
     *
     * @param mixed $object
     */
    public static function injectClassesAndComponentsIntoObject($object)
    {
        $reflection = new \ReflectionObject($object);
        $properties = static::getClassProperties($reflection);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            if (!is_null($property->getValue($object))) {
                continue;
            }

            $comment = $property->getDocComment();
            $options = array();
            if (static::hasParameter(self::INJECTION_PARAMETER_OPTIONS, $comment)) {
                $optionString = static::resolveParamterValueFromDocComment(self::INJECTION_PARAMETER_OPTIONS, $comment);
                $options = explode(',', trim(str_replace(array('(',')'), array('',''), $optionString)));
            }
            
            if (static::hasParameter(self::INJECTION_PARAMETER, $comment)) {
                // Do some magic.
                // Check if it is a class
                $value = static::resolveParamterValueFromDocComment(self::INJECTION_PARAMETER, $comment);
                if (strpos($value, '\\') !== false) {
                    $property->setValue($object, ObjectFactory::singleton($value, $options));
                }
            } elseif (static::hasParameter(self::INJECTION_PARAMETER_INSTANCE, $comment)) {
                $value = static::resolveParamterValueFromDocComment(self::INJECTION_PARAMETER_INSTANCE, $comment);
                if (strpos($value, '\\') !== false) {
                    $property->setValue($object, ObjectFactory::create($value, $options));
                }
            } elseif (static::hasParameter(self::INJECTION_PARAMETER_SINGLETON, $comment)) {
                $value = static::resolveParamterValueFromDocComment(self::INJECTION_PARAMETER_SINGLETON, $comment);
                if (strpos($value, '\\') !== false) {
                    $property->setValue($object, ObjectFactory::singleton($value, $options));
                }
            }
        }
    }
}
