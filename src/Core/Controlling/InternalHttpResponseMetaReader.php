<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\HttpController;

class InternalHttpResponseMetaReader
{
    /**
     * Extracts the meta information from an internal send response message
     *
     * @param mixed $responseObject
     * @return stdClass|null
     */
    public static function getMetaInformation($responseObject)
    {
        if (isset($responseObject->{HttpController::JSON_META_RESPONSE_KEYWORD})) {
            $metaObject = $responseObject->{HttpController::JSON_META_RESPONSE_KEYWORD};
            return $metaObject;
        }
        return null;
    }

    /**
     * Extracts one meta field
     *
     * @param string $key
     * @param mixed $responseObject
     * @param mixed $fallback
     * @return mixed|null
     */
    public static function getSpecificMetaKey(string $key, $responseObject, $fallback = null)
    {
        if (( $metaObject = self::getMetaInformation($responseObject) )) {
            if (isset($metaObject->{$key})) {
                return $metaObject->{$key};
            }
        }
        return $fallback;
    }

    /**
     * Extracts the statusCode field ( same as HTTP response code )
     *
     * @param mixed $responseObject
     * @return integer|null
     */
    public static function getStatusCode($responseObject) : ?int
    {
        return self::getSpecificMetaKey('statusCode', $responseObject);
    }

    /**
     * Extracts the success flag
     *
     * @param mixed $responseObject
     * @return boolean|null
     */
    public static function getSuccessFlag($responseObject) : ?bool
    {
        return self::getSpecificMetaKey('success', $responseObject);
    }

    /**
     * Extracts the error flag
     *
     * @param mixed $responseObject
     * @return boolean|null
     */
    public static function getErrorFlag($responseObject) : ?bool
    {
        return self::getSpecificMetaKey('error', $responseObject);
    }

    /**
     * Extracts the meta message
     *
     * @param mixed $responseObject
     * @return string|null
     */
    public static function getMessage($responseObject) : ?string
    {
        return self::getSpecificMetaKey('message', $responseObject);
    }

    /**
     * Extracts the unix time formatted timestamp
     *
     * @param mixed $responseObject
     * @return integer|null
     */
    public static function getTimestamp($responseObject) : ?int
    {
        return self::getSpecificMetaKey('timestamp', $responseObject);
    }

    /**
     * Extracts the date time ( Y-m-d H:i:s )
     *
     * @param mixed $responseObject
     * @return mixed
     */
    public static function getDateTime($responseObject)
    {
        return self::getSpecificMetaKey('datetime', $responseObject);
    }
}
