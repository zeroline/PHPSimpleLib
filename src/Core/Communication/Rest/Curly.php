<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Communication\Rest;

use PHPSimpleLib\Core\Communication\EnumHTTPVerbs;

/**
 * Class Wrapper for simplified curl request.
 * Mainly used for sending AND requesting JSON !
 */
class Curly
{

    /**
     * Custom header can be predefined and will be added to the request.
     *
     * @var array
     */
    public static $customHeader = array();
/**
     * Generates a curl instance without excecuting.
     * Builds header, content and basic configuration.
     *
     * @param string $url
     * @param string $type indicating the HTTP verb {@see PHPSimpleLib\Core\Communication\EnumHTTPVerbs }
     * @param mixed $params can be a string or array. A string will be interpreted as an encoded json object
     * @return resource
     *
     * @throws \Exception if a $type is provided that is not supported.
     */
    public static function init(string $url, string $type, $params = array())
    {
        $type = strtoupper($type);
        $allowedVerbs = array(
            EnumHTTPVerbs::HTTP_VERB_GET,
            EnumHTTPVerbs::HTTP_VERB_POST,
            EnumHTTPVerbs::HTTP_VERB_PUT,
            EnumHTTPVerbs::HTTP_VERB_DELETE,
            EnumHTTPVerbs::HTTP_VERB_PATCH
        );
        if (!in_array($type, $allowedVerbs)) {
            throw new \Exception("Unsupported HTTP verb used.");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        if (
            in_array($type, array(
            EnumHTTPVerbs::HTTP_VERB_POST,
            EnumHTTPVerbs::HTTP_VERB_PUT,
            EnumHTTPVerbs::HTTP_VERB_DELETE,
            EnumHTTPVerbs::HTTP_VERB_PATCH))
        ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            if (is_string($params)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($params)), static::$customHeader));
            }
        } elseif ($type === EnumHTTPVerbs::HTTP_VERB_GET) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($params);
            if (count(static::$customHeader)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, static::$customHeader);
            }
        }

        curl_setopt($ch, CURLOPT_URL, utf8_decode($url));
        return $ch;
    }

    /**
     * Post an array of data.
     * JSON response is expected an json_decode will be excecuted.
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function post(string $url, array $params = array())
    {
        return json_decode(curl_exec(static::init($url, EnumHTTPVerbs::HTTP_VERB_POST, $params)));
    }

    /**
     * Post the given data as a json_encoded string. json_encode will be automaticly cast
     * on the data.
     * JSON response is expected an json_decode will be excecuted.
     *
     * @param string $url
     * @param mixed $data
     * @return mixed
     */
    public static function postJson(string $url, $data = null)
    {
        return json_decode(curl_exec(static::init($url, EnumHTTPVerbs::HTTP_VERB_POST, json_encode($data))));
    }

    /**
     * Perform a GET request. The data as an array will be concatenated and encoded.
     * JSON response is expected an json_decode will be excecuted.
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function get(string $url, array $params = array())
    {
        return json_decode(curl_exec(static::init($url, 'get', $params)));
    }

    /**
     * Short individual function for posting a json string to a
     * url. No special headers or configuration will happen.
     *
     * A raw curl_exec result will be returned.
     *
     * @param string $url
     * @param string $data
     * @return mixed
     */
    public static function nakedPost(string $url, string $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
        $result = curl_exec($ch);
        return $result;
    }
}
