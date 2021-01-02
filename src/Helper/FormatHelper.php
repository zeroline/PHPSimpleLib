<?php

namespace PHPSimpleLib\Helper;

final class FormatHelper
{
    /**
     * Return a readable file size string.
     *
     * @param integer $bytes
     * @param boolean $binaryPrefix
     * @return string
     */
    public static function getNiceFileSizeFromBytes(int $bytes, bool $binaryPrefix = true): string
    {
        if ($binaryPrefix) {
            $unit = array('B','KiB','MiB','GiB','TiB','PiB');
            if ($bytes === 0) {
                return '0 ' . $unit[0];
            }
            return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . ' ' . (isset($unit[$i]) ? $unit[$i] : 'B');
        } else {
            $unit = array('B','KB','MB','GB','TB','PB');
            if ($bytes === 0) {
                return '0 ' . $unit[0];
            }
            return @round($bytes / pow(1000, ($i = floor(log($bytes, 1000)))), 2) . ' ' . (isset($unit[$i]) ? $unit[$i] : 'B');
        }
    }
}
