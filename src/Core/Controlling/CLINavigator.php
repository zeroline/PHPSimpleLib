<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\ModuleManager;
use PHPSimpleLib\Core\Controlling\Traits\ConsoleIOTrait;
use PHPSimpleLib\Core\ObjectFactory\Singleton;

class CLINavigator
{
    use Singleton;
                                                                                                                                                                                                                                                                                                         use ConsoleIOTrait;


    private const COMMAND_CONTROLLER_HELP_ACTION = 'help';

    public function resolveCLI(): void
    {
        $p = CliRequestParser::getInstance();
        $p->parse();
        if ($p->getParseResult() === Parser::RESULT_FOUND) {
            call_user_func_array(array($p->getParsedController(), $p->getParsedAction()), $p->getParsedParameter());
        } else if ($p->getParseResult() === Parser::RESULT_PARTIALLY_FOUND || $p->getParseResult() === Parser::RESULT_NOT_FOUND) {
            $this->navigationHelper($p->getParsedModule() ?? null);
        } else {
            trigger_error('CLI parser error.', E_USER_ERROR);
        }
    }

    private function navigationHelper(?string $moduleName = null): void
    {
        $this->outLine('Unknown command! Listing available commands...');
        $mm = ModuleManager::getInstance();
        $c = $mm->getCommandControllerModules($moduleName);
        foreach ($c as $foundModuleName => $commandController) {
            $this->outLine("");
            $this->outLine('Module: ' . $foundModuleName);
            if (count($commandController) === 0) {
                $this->outLine('No commands available.');
                continue;
            }
            foreach ($commandController as $cc) {
                $this->outLine('CommandController: ' . $mm->getSimplifiedCommandControllerName($cc));
                call_user_func_array(array($cc, self::COMMAND_CONTROLLER_HELP_ACTION), array());
                $this->outLine("");
            }
        }
    }
}
