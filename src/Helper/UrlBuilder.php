<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class UrlBuilder
{
    /**
     * Returns a base url string using the current server
     * properties
     *
     * @return string
     */
    private static function getBaseUrl() : string
    {
        return sprintf(
            "%s://%s" . DIRECTORY_SEPARATOR,
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );
    }
    
    /**
     * Generates an url using the getBaseUrl function and adding
     * the given path
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path) : string
    {
        return static::getBaseUrl() . $path;
    }
}
