<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Communication\Rest;

/**
 * Class Wrapper to use for simplyfied file_get_contents post
 * and get requests
 */
final class SimpleRequest
{
    /**
     * Perform a post request via file_getcontext.
     * Default header equals a form post header
     *
     * @param string $url
     * @param mixed $data
     * @param string $header
     * @return string
     */
    public static function post($url, $data = null, $header = 'Content-type: application/x-www-form-urlencoded\r\n')
    {
        $options = array(
            'http' => array(
                'header'  => $header,
                'method'  => 'POST',
                'content' => (is_array($data) || is_object($data) ? http_build_query($data) : $data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * Perform a get request using file_get_context.
     * Data in the form "key" => "value" is automaticly attached to the
     * url an urlencoded.
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function get($url, array $data = array())
    {
        if (!String::endsWith($url, '?')) {
            $url = $url . '?';
        } elseif (!String::endsWith($url, '&') && strpos($url, '?') !== false) {
            $url = $url . '&';
        }

        $dataString = array();
        foreach ($data as $k => $v) {
            $dataString[] = urlencode($k) . '=' . urlencode($v);
        }
        $url = $url . implode('&', $dataString);
        return file_get_contents($url);
    }
}
