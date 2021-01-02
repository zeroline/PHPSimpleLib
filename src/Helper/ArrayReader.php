<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class ArrayReader
{
    /**
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public static function getConfig(array $config, $key, $fallback = null)
    {
        return static::parseArrayPath($key, $config) ?: (is_callable($fallback) ? call_user_func($fallback) : $fallback );
    }

    /**
     *
     * @param string $key
     * @param array $array
     * @return mixed
     */
    private static function parseArrayPath($key, array $array)
    {
        $parts = explode('.', $key);
        if (sizeof($parts) === 1) {
            if (array_key_exists($parts[0], $array)) {
                return $array[$parts[0]];
            }
        } else {
            if (array_key_exists($parts[0], $array)) {
                return static::parseArrayPath(implode('.', array_slice($parts, 1)), $array[$parts[0]]);
            }
        }
        return null;
    }
}
