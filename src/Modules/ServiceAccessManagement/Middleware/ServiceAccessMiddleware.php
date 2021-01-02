<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Middleware;

use PHPSimpleLib\Core\Controlling\Middleware;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessCommunicationService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessModel;

class ServiceAccessMiddleware extends Middleware
{
    const CONTROLLER_FIELD_NAME = 'requestData';
    const CONTROLLER_ACCESS_FIELD_NAME = 'appAccess';
    public function process()
    {
        if ($this->getController() instanceof \PHPSimpleLib\Core\Controlling\HttpController) {
            $requestAppKey = $this->getController()->customHeader(ServiceAccessCommunicationService::HEADER_APP_KEY);
            $requestHash = $this->getController()->customHeader(ServiceAccessCommunicationService::HEADER_HASH);
            $requestIV = $this->getController()->customHeader(ServiceAccessCommunicationService::HEADER_IV);
            if ($requestAppKey && $requestHash && $requestIV) {
                $appAccess = ServiceAccessService::findClientByAppKey($requestAppKey);
                if (ServiceAccessService::hasClientAccess($appAccess)) {
                    if (ServiceAccessCommunicationService::isRequestValid($requestHash, $requestIV, $appAccess)) {
                            $this->getController()->injectMiddlewareField(self::class, self::CONTROLLER_ACCESS_FIELD_NAME, $appAccess);
                            // Check if there is a request body to parse
                        $requestBody = $this->getController()->body();
                        if ($requestBody && !empty($requestBody)) {
                            try {
                                        $jwt = ServiceAccessCommunicationService::extractJWTFromMessageString($appAccess->getAppKey(), $appAccess->getAppSecret(), $requestBody);
                                        $this->getController()->injectMiddlewareField(self::class, self::CONTROLLER_FIELD_NAME, $jwt->{ServiceAccessCommunicationService::JWT_DATA_FIELD_NAME});
                            } catch (\Exception $ex) {
                                return $this->getController()->responseAccessDenied($ex->getMessage());
                            }
                        }
                        return true;
                    }
                }
            }
        } else {
            throw new \Exception('Invalid controller type for middleware.');
        }
        return $this->getController()->responseAccessDenied();
    }
}
