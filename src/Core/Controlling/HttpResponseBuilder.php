<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\HttpController;

class HttpResponseBuilder
{
    public static function buildBasicResponseArray($data, bool $success, string $message, int $code) : array
    {
        $response = array_reverse(array_merge(array(HttpController::JSON_META_RESPONSE_KEYWORD => array(
            'success' => (bool)$success,
            'error' => (bool)!$success,
            'message' => $message,
            'statusCode' => $code,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s')
        )), (array)json_decode(json_encode($data))));
        return $response;
    }
}
