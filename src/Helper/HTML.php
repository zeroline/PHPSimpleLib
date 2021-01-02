<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class HTML
{
    /**
     *
     * @param string $file
     * @param string $mime
     * @return string
     */
    public static function generateImageDataUriFromFile($file, $mime = null)
    {
        $contents = file_get_contents($file);
        $base64 = base64_encode($contents);
        if (is_null($mime)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file);
            finfo_close($finfo);
        }

        return static::embeddedImageSourceFromData($mime, $base64);
    }

    /**
     * Generates the img source string to embedd images
     *
     * @param string $mime
     * @param string $data
     * @return string
     */
    public static function embeddedImageSourceFromData($mime, $data)
    {
        return "data:" . $mime . ";base64," . $data;
    }
}
