<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Logging;

use PHPSimpleLib\Core\Logging\EnumLogLevel;
use PHPSimpleLib\Core\Logging\AbstractLogger;

use PHPSimpleLib\Core\Filesystem\RuntimeFileSystemHelper;

/**
 * Logger class for logging messages to the applications filesystem.
 */
final class RuntimeFileSystemLogger extends AbstractLogger
{
    /**
     * Using this mode results in only one log file. 
     * Files older than today will be removed if possible
     */
    public const ROTATION_MODE_DAILY_SINGLE_FILE_ONLY = 'dailySingle';

    /**
     * Using this mode results in one active log file and
     * renaming older files ( infinite ) using their last 
     * modified date.
     * NOTE: only a daily cron job can ensure that one file 
     * would contain data for one day. This procedure is here called with 
     * the application an can result in archived log files containing
     * data for multiple days at least.
     */
    public const ROTATION_MODE_DAILY_ARCHIVE = 'dailyArchive';

    /**
     * Using this mode will result in only on file and by calling the clearing
     * method all previous data will be deleted
     */
    public const ROTATION_MODE_ONE_RUN_ONLY = 'oneRunOnly';

    /**
     * This logger uses multiple config entries. This is the key where the
     * value can be found.
     */
    public const CONFIG_KEY_FILE = 'logFile';

    /**
     * Config key for indicating another date time config value
     */
    public const CONFIG_KEY_DATETIME_FORMAT = "dateTimeFormat";

    /**
     * Config key for setting a rotation mode
     */
    public const CONFIG_KEY_ROTATION_MODE = 'rotationMode';

    /**
     * A default value is provided to the config
     * @see PHPSimpleLib.php
     */
    public const DEFAULT_LOG_FILE = "Runtime/Log/PHPSimpleLib.log";

    /**
     * Default rotation mode if no config is available
     */
    public const DEFAULT_ROTATION_MODE = self::ROTATION_MODE_ONE_RUN_ONLY;

    /**
     * The default date time format used in the log message
     */
    public const DEFAULT_DATE_TIME_FORMAT = \DateTime::ATOM;

    /**
     * Logs the message after interpolation to the log file,
     * appending the entry to the file's end.
     *
     * @param string $level {@see EnumLogLevel}
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $level, string $message, array $context = array()) : void
    {
        $baseMessageString = $this->interpolate($message, $context);
        $dateTime = date($this->getConfig(self::CONFIG_KEY_DATETIME_FORMAT, self::DEFAULT_DATE_TIME_FORMAT));
        $finalMessageString = $dateTime . ' [' . $level . '] ' . $baseMessageString . PHP_EOL;
        $filename = $this->getConfig(self::CONFIG_KEY_FILE, self::DEFAULT_LOG_FILE);
        
        RuntimeFileSystemHelper::createOrAppend($filename, $finalMessageString, RuntimeFileSystemHelper::MODE_OWNER_GROUP_WRITEONLY);
    }

    public function clearing() : void {
        $filename = $this->getConfig(self::CONFIG_KEY_FILE, self::DEFAULT_LOG_FILE);
        $lastChange = filectime($filename);
        $olderThanOneDay = (( (time() - $lastChange)/60/60/24 ) >= 1);
        $rotationMode = $this->getConfig(self::CONFIG_KEY_ROTATION_MODE, self::DEFAULT_ROTATION_MODE);
        switch($rotationMode) {
            case self::ROTATION_MODE_DAILY_SINGLE_FILE_ONLY:
                if($olderThanOneDay) {
                    file_put_contents($filename, '');
                }
            break;
            case self::ROTATION_MODE_DAILY_ARCHIVE:
                if($olderThanOneDay) {
                    copy($filename, $filename.'.'.date('Y-m-d').'.backup');
                    file_put_contents($filename, '');
                }
            break;
            case self::ROTATION_MODE_ONE_RUN_ONLY:
                file_put_contents($filename, '');
            break;
            default:
                throw new \Exception('Rotation mode "'.$rotationMode.'" for '.__CLASS__.' is not valid.');
            break;
        }
    }
}
