<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Middleware;

use PHPSimpleLib\Core\Controlling\Middleware;

class RequestLimitMiddleware extends Middleware {
    public const DEFAULT_TIMESPAN = 600;
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_CONTROLLER_WIDE = true;
    
    private const SERVER_KEY_NAME = 'SERVER_NAME';
    private const SERVER_KEY_IP = 'REMOTE_ADDR';

    public function process()
    {
        if ($this->getController() instanceof \PHPSimpleLib\Core\Controlling\HttpController) {
            $name = $this->getConfig('name', $this->getController()->getSimplifiedControllerName());
            $timeSpan = $this->getConfig('timeSpan', self::DEFAULT_TIMESPAN);
            $limit = $this->getConfig('limit', self::DEFAULT_LIMIT);

            $apcuKey = $this->getController()->getHeader(self::SERVER_KEY_NAME);
            $apcuKey.= '~'.$name.':';
            $apcuKey.= $this->getController()->getHeader(self::SERVER_KEY_IP);

            /*if(function_exists("apcu_fetch")) {
                $tries = (int)apcu_fetch($apcuKey);

                if ($tries >= $limit) {
                    return $this->getController()->responseError();
                } else {
                    apcu_inc($apcuKey, $tries+1, $timeSpan);
                    return true;
                }
            } else {

            }*/          
        } else {
            throw new \Exception('Invalid controller type for middleware.');
        }
        return $this->getController()->responseError();
    }
}