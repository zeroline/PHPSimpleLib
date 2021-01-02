<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Middleware;

use PHPSimpleLib\Core\Controlling\Middleware;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessCommunicationService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessModel;

class HeaderFieldCheckMiddleware extends Middleware
{
    public function process()
    {
        if ($this->getController() instanceof \PHPSimpleLib\Core\Controlling\HttpController) {
            $fieldsToChecks = $this->getConfig('fields');
            foreach ($fieldsToChecks as $fieldName) {
                $fieldData = $this->getController()->customHeader('X-' . $fieldName);
                if ($fieldData) {
                    $this->getController()->injectMiddlewareField(self::class, $fieldName, $fieldData);
                } else {
                    return $this->getController()->responseError();
                }
            }
            return true;
        } else {
            throw new \Exception('Invalid controller type for middleware.');
        }
        return $this->getController()->responseError();
    }
}
