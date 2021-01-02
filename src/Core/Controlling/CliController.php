<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Controller;
use PHPSimpleLib\Core\Controlling\Traits\ConsoleIOTrait;
use PHPSimpleLib\Helper\StringHelper;

class CliController extends Controller
{
    use ConsoleIOTrait;

    /**
     * Healthcheck function
     *
     * @return void
     */
    public function healthcheckAction() : void
    {
        $this->logInfo('I\'m fine :)');
    }

    /**
     * Lists all available actions
     *
     * @return void
     */
    public function helpAction() : void
    {
        $calledClass = get_called_class();
        $methods = get_class_methods($calledClass);
        $this->outLine('==============================');
        $this->outLine('Command list:');
        $this->outLine('==============================');
        foreach ($methods as $methodName) {
            if (StringHelper::endsWith($methodName, Controller::METHOD_SUFFIX)) {
                $this->outLine("\t".substr($methodName, 0, -(strlen(Controller::METHOD_SUFFIX))));
            }
        }
        $this->outLine('==============================');
    }
}
