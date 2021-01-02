<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class Renderer
{
    /**
     *
     * @param string $filename
     * @param array $data
     * @param string $extension
     * @return string
     */
    public static function renderFile($filename, array $data = array())
    {
        if (extension_loaded('gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start();
        }
        
        extract($data);

        
        require $filename;

        $result = ob_get_clean();
        return $result;
    }
    
    /**
     *
     * @param string $html
     * @param array $data
     * @return string
     */
    public static function renderHtml($html, array $data = array())
    {
        if (extension_loaded('gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start();
        }
        
        extract($data);

        eval('?> ' . $html . ' ');
        
        return ob_get_clean();
    }

    /**
     * Simple & quick text renderer.
     *
     * @param string $message
     * @param array $context
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function interpolate(string $message, array $context = array(), $prefix = '{{', $suffix = '}}') : string
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, "__toString"))) {
                $replace[$prefix . $key . $suffix] = $val;
            }
        }
    
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
