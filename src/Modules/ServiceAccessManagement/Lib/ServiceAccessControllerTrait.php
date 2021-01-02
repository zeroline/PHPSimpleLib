<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Lib;

use PHPSimpleLib\Modules\ServiceAccessManagement\Middleware\ServiceAccessMiddleware;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessCommunicationService;
use PHPSimpleLib\Core\Controlling\HttpResponseBuilder;

trait ServiceAccessControllerTrait
{
    /**
     * Returns the secure requested data parsed from a validated JWT
     *
     * @return mixed
     */
    public function getRequestData()
    {
        return $this->getInjectedMiddlewareField(ServiceAccessMiddleware::class, ServiceAccessMiddleware::CONTROLLER_FIELD_NAME);
    }

    /**
     * Overrides the base response function for sending JWT strings
     * back.
     *
     * Generic response
     * Switches the content type to text/plain with base64 transfer
     * Sets the given HTTP response code
     *
     * @param array $data
     * @param boolean $success
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function response($data, bool $success, string $message, int $code): string
    {
        $appAccess = $this->getInjectedMiddlewareField(ServiceAccessMiddleware::class, ServiceAccessMiddleware::CONTROLLER_ACCESS_FIELD_NAME);
        if (!$appAccess && $code !== 403 && $code < 500) {
            throw new \Exception('Missing application service access model in controller. Should be injected from "' . ServiceAccessMiddleware::class . '"');
        } elseif (!$appAccess && ($code === 403 || $code >= 500)) {
            http_response_code($code);
            $this->contentHeader(self::CONTENT_TYPE_JSON);
            $response = HttpResponseBuilder::buildBasicResponseArray($data, $success, $message, $code);
            return json_encode($response);
        }

        http_response_code($code);
        $this->contentHeader(self::CONTENT_TYPE_TEXT_PLAIN_JWT);
        $response = HttpResponseBuilder::buildBasicResponseArray($data, $success, $message, $code);
        $jwtString = ServiceAccessCommunicationService::createMessageDataJWTString($appAccess->getAppKey(), $appAccess->getAppSecret(), $response);
        return $jwtString;
    }
}
