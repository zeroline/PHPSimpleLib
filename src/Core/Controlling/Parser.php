<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Controller as Controller;

abstract class Parser
{
    use \PHPSimpleLib\Core\ObjectFactory\Singleton;


    public const RESULT_UNKNOWN = 0;
    public const RESULT_FOUND = 1;
    public const RESULT_PARTIALLY_FOUND = 2;
    public const RESULT_NOT_FOUND = 3;
    protected int $parseResult = self::RESULT_UNKNOWN;
    abstract public function parse();
    abstract public function getParsedModule(): ?string;
    abstract public function getParsedController(): ?Controller;
    abstract public function getParsedAction(): ?string;
    abstract public function getParsedParameter(): array;

    public function getParseResult(): int
    {
        return $this->parseResult;
    }

    private function setParseResult(int $parseResult): void
    {
        $this->parseResult = $parseResult;
    }

    protected function setNotFound(): void
    {
        $this->setParseResult(self::RESULT_NOT_FOUND);
    }

    protected function setPartiallyFound(): void
    {
        $this->setParseResult(self::RESULT_PARTIALLY_FOUND);
    }

    protected function setFound(): void
    {
        $this->setParseResult(self::RESULT_FOUND);
    }
}
