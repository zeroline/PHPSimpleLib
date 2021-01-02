<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Lib;

use PHPSimpleLib\Core\Controlling\HttpCrudController;
use PHPSimpleLib\Modules\ServiceAccessManagement\Middleware\ServiceAccessMiddleware;
use PHPSimpleLib\Modules\ServiceAccessManagement\Lib\ServiceAccessControllerTrait;

class ServiceAccessHttpCrudController extends HttpCrudController
{
    use ServiceAccessControllerTrait;


    public function __construct()
    {
        $this->addMiddleware(ServiceAccessMiddleware::class);
    }
}
