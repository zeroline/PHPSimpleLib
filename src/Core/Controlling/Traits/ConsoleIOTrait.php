<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling\Traits;

use PHPSimpleLib\Core\Logging\EnumLogLevel;
use PHPSimpleLib\Core\Logging\OutputFormats;

trait ConsoleIOTrait {

    /**
     * 
     * @return string 
     */
    protected function getDateForOutput() : string {
        return '['.date(OutputFormats::DATE_LONG).']';
    }
    /**
     *
     * @param string $data
     */
    protected function out($data)
    {
        if (!is_string($data)) {
            $data = print_r($data, true);
        }
        echo $data;
        flush();
    }

    /**
     *
     * @param mixed $data
     * @return void
     */
    public function outLine($data)
    {
        if (!is_string($data)) {
            $data = print_r($data, true);
        }
        echo $data . PHP_EOL;
        flush();
    }

    /**
     * 
     * @param string $question 
     * @param string $positive 
     * @param string $negative 
     * @return bool 
     */
    protected function confirm(string $question, string $positive = 'y', string $negative = 'n') : bool {
        $this->out($question.' ('.$positive.'/'.$negative.') ');
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        if(strtolower(trim($line)) == $positive){
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $data
     * @param boolean $autoEOL
     */
    protected function log($data, $type = EnumLogLevel::INFO, $autoEOL = true)
    {
        if (!is_string($data)) {
            $data = print_r($data, true);
        }
        echo $this->getDateForOutput(). ' [' . $type . '] ' . $data . ($autoEOL ? PHP_EOL : '');
        flush();
    }

    /**
     * Log everything to the console
     * Auto-stringify objects and arrays
     *
     * @param array $dataToLog
     * @param string $type
     * @return void
     */
    private function simplifiedLog(array $dataToLog, string $type) : void
    {
        $printData = array();
        foreach ($dataToLog as $data) {
            if (!is_string($data)) {
                $printData[] =  print_r($data, true);
            } else {
                $printData[] = $data;
            }
        }
        $printDataString = implode(' ', $printData);
        $this->out($this->getDateForOutput() . ' [' . $type . '] ' . $printDataString . PHP_EOL);
    }

    /**
     * Log a debug message to the console
     *
     * @return void
     */
    protected function logDebug() : void
    {
        $this->simplifiedLog(func_get_args(), EnumLogLevel::DEBUG);
    }

    /**
     * Log an info message to the console
     *
     * @return void
     */
    protected function logInfo() : void
    {
        $this->simplifiedLog(func_get_args(), EnumLogLevel::INFO);
    }

    /**
     * Log a warning message to the console
     *
     * @return void
     */
    protected function logWarning() : void
    {
        $this->simplifiedLog(func_get_args(), EnumLogLevel::WARNING);
    }

    /**
     * Log an error message to the console
     *
     * @return void
     */
    protected function logError() : void
    {
        $this->simplifiedLog(func_get_args(), EnumLogLevel::ERROR);
    }
}