<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Filesystem;

final class RuntimeFileSystemHelper
{
    public const MODE_ALL_READONLY = 0444;
    public const MODE_ALL_WRITEONLY = 0222;

    public const MODE_OWNER_WRITEONLY = 0200;
    public const MODE_GROUP_WRITEONLY = 0020;
    public const MODE_OWNER_GROUP_WRITEONLY = 0220;

    public const MODE_OWNER_RW = 0600;
    public const MODE_GROUP_RW = 0060;
    public const MODE_OWNER_GROUP_RW = 0660;
    public const MODE_ALL_RW = 0666;

    public const MODE_ALL_ALL = 0777;

    /**
     * Creates all directories for the given path if they're not
     * existing.
     *
     * @param string $path
     * @param integer $mode
     * @return boolean
     */
    private static function makePath(string $path, int $mode = self::MODE_ALL_ALL) : bool
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
     
        if (is_dir($dir)) {
            return true;
        } else {
            if (static::makePath($dir, $mode)) {
                if (mkdir($dir)) {
                    chmod($dir, $mode);
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Creates or appends the given data to the file.
     * Directory creation takes place automaticly
     *
     * @param string $pathToFile
     * @param string $data
     * @param integer $mode
     * @return void
     */
    public static function createOrAppend(string $pathToFile, string $data, int $mode = self::MODE_ALL_ALL) : void
    {
        static::makePath($pathToFile);
        file_put_contents($pathToFile, $data, FILE_APPEND);
    }
}
