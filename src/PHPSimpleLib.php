<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib;

use PHPSimpleLib\Core\Controlling\CLINavigator;
use PHPSimpleLib\Helper\Autoloader;
use PHPSimpleLib\Helper\ArrayReader;
use PHPSimpleLib\Core\Controlling\ModuleManager;

use PHPSimpleLib\Core\Data\DBConnectionManager;

use PHPSimpleLib\Core\Controlling\HTTPNavigator;
use PHPSimpleLib\Core\Logging\RuntimeFileSystemLogger;
use PHPSimpleLib\Core\Logging\EnumLogLevel;

final class PHPSimpleLib
{
    private $config = array();
    
    public const RUNMODE_REST_JSON = 'restJson';
    public const RUNMODE_CLI = 'cli';
    public const RUNMODE_SOCKET = 'socket';
    public const RUNMODE_HTML = 'html';
    public const RUNMODE_HTTP = 'http';

    public const CONFIG_KEY_DEBUG = 'debug';
    
    private function getConfig($key, $fallback = null)
    {
        return ArrayReader::getConfig($this->config, $key, $fallback);
    }
    
    public function bootstrap(array $config = array())
    {
        /**
         * Store the config
         */
        $this->config = $config;

        /**
         * Get basic error handling config
         */
        error_reporting($this->getConfig('error.level', E_ALL));
        ini_set('log_errors', $this->getConfig('error.log_errors', '1'));
        ini_set('display_errors', $this->getConfig('error.display_errors', '0'));

        // Set timezone
        date_default_timezone_set($this->getConfig('timezone', 'Europe/Berlin'));

        /**
         * Set right encoding
         * We use UTF-8 ( mb4 in MySQL )
         */
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        
        /**
         * Require and activate the autoloader
         */
        require 'Helper/Autoloader.php';
        if($this->getConfig('autoloader', 'composer') === 'composer') {
            Autoloader::useComposerAutoloader();
        } else {
            Autoloader::useDefaultAutoloader();
        }

        /**
         * Get the DB config and instanciate the DBConnectionManager
         */
        DBConnectionManager::getInstance($this->getConfig('db', array()));

        /**
         * Get log hanlder classes and configuration from the config.
         */
        $logging = $this->getConfig('logging', array());

        foreach ($logging as $loggerClass => $config) {
            $loggerClass::getInstance($config);
            $loggerClass::getInstance()->clearing();
        }
    }
    
    public function run()
    {
        /**
         * Prepare the module manager and build the instances
         */
        $mm = ModuleManager::getInstance();
        
        if (!$this->getConfig(self::CONFIG_KEY_DEBUG, false)) {
            $mm->prepareControllerInstances();
        } else {
            $startTimePreparing = microtime(true);
            $mm->prepareControllerInstances();
            $endTimePreparing = microtime(true);
            $durationPreparing = $endTimePreparing - $startTimePreparing;
            RuntimeFileSystemLogger::getInstance()->log(EnumLogLevel::DEBUG, 'Function prepareControllerInstances() took {time}s.', array('time' => $durationPreparing));
        }
        
        /**
         * Why are we here ?
         */
        switch ($this->getConfig('runmode')) {
            case self::RUNMODE_SOCKET:
                throw new \RuntimeException('Runmode "socket" not implemented.');
               break;
            case self::RUNMODE_CLI:
                CLINavigator::getInstance()->resolveCLI();
                break;
            case self::RUNMODE_REST_JSON:
            case self::RUNMODE_HTML:
            case self::RUNMODE_HTTP:
            default:
                HTTPNavigator::getInstance()->resolveHTTP();
                break;
        }
    }
}
