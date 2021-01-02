<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Filesystem;

class TempFile
{
    /**
     * Creates a temporary file and writes the given data into it.
     * Returns the created file name
     *
     * @param string $data
     * @return string
     */
    public static function write($data, $prefix = '')
    {
        $temp_file = tempnam(sys_get_temp_dir(), $prefix);
        if (file_put_contents($temp_file, $data)) {
            return $temp_file;
        }
        return null;
    }

    /**
     *
     * @param string $filename
     * @return string
     */
    public static function read($filename)
    {
        return file_get_contents($filename);
    }
}
