<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\ModuleManager;
use PHPSimpleLib\Core\ObjectFactory\Singleton;

class HTTPNavigator
{
    use Singleton;

    public function resolveHTTP() : void {
        $p = HttpRequestParser::getInstance();
        $p->setRouteMappings(ModuleManager::getInstance()->getAllRouteMappings());
        $p->parse();
        if ($p->getParseResult() === Parser::RESULT_FOUND) {
            try {
                echo call_user_func_array(array($p->getParsedController(), $p->getParsedAction()), $p->getParsedParameter());
            } catch (\Exception $ex) {
                http_response_code(500);
                header("Content-Type: application/json; charset=utf-8", true);
                echo json_encode(HttpResponseBuilder::buildBasicResponseArray(array(), false, $ex->getMessage(), 500));
            }
        } else {
            http_response_code(404);
            header("Content-Type: application/json; charset=utf-8", true);
            echo json_encode(HttpResponseBuilder::buildBasicResponseArray(array(), false, 'Not found', 404));
        }
    }
}
