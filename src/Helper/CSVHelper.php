<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class CSVHelper
{
    public static function build(array $columns, array $data, array $functions = array(), array $ignore = array(), $filename = 'Export.csv', $capsule = '"', $seperator = ';', $lineend = PHP_EOL, array $extraFunctions = array())
    {
        $csv_content = "";
        $deepDataIndicator = '.';
        if (count($data) == 0) {
            return chr(255) . chr(254) . mb_convert_encoding($csv_content, "UCS-2LE", "auto");
        }

        if (sizeof($columns) == 0) {
//Deep data
            foreach ($data[0] as $key => $value) {
                if (in_array($key, $columns)) {
                    continue;
                }
                if (in_array($key, $ignore)) {
                    continue;
                }

                //Deep data
                if (is_object($value)) {
                    foreach ($value as $subKey => $subValue) {
                        if (in_array($key . $deepDataIndicator . $subKey, $columns)) {
                            continue;
                        }
                        if (in_array($key . $deepDataIndicator . $subKey, $ignore)) {
                            continue;
                        }
                        $columns[$key . $deepDataIndicator . $subKey] = $key . $deepDataIndicator . $subKey;
                    }
                } else {
                    $columns[$key] = $key;
                }
            }
        }

        $csv_content .= implode($seperator, array_values($columns)) . $lineend;
//Collection data
        foreach ($data as $event) {
            $row = array();
            $event = (object)$event;
            foreach ($columns as $column => $title) {
                if (in_array($column, $ignore)) {
                    continue;
                }
                //$row[$column] = null;

                if (array_key_exists($column, $functions)) {
                    $row[] = $capsule . $functions[$column]($event->{$column}) . $capsule;
                } elseif (array_key_exists($column, $extraFunctions)) {
                    $funcData = $extraFunctions[$column];
                    $row[] = $funcData[1]($event->{$funcData[0]});
                } else {
                    if (strpos($column, $deepDataIndicator) !== false) {
                                list($firstKey, $secondKey) = explode($deepDataIndicator, $column);
                        if (isset($event->{$firstKey}->{$secondKey})) {
                            $row[] = $capsule . $event->{$firstKey}->{$secondKey} . $capsule;
                        } else {
                            $row[] = null;
                        }
                    } else {
                        if (isset($event->{$column})) {
                            $row[] = $capsule . $event->{$column} . $capsule;
                        } else {
                            $row[] = null;
                        }
                    }
                }
            }
            $csv_content .= implode($seperator, $row) . $lineend;
        }
        return chr(255) . chr(254) . mb_convert_encoding($csv_content, "UCS-2LE", "auto");
    }

    public static function download(array $columns, array $data, array $functions = array(), array $ignore = array(), $filename = 'Export.csv', $capsule = '"', $seperator = ';', $lineend = PHP_EOL, array $extraFunctions = array())
    {
        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        echo static::build($columns, $data, $functions, $ignore, $filename, $capsule, $seperator, $lineend, $extraFunctions);
        exit;
    }

    public static function save(array $columns, array $data, array $functions = array(), array $ignore = array(), $filename = 'Export.csv', $capsule = '"', $seperator = ';', $lineend = PHP_EOL, array $extraFunctions = array())
    {
        file_put_contents($filename, static::build($columns, $data, $functions, $ignore, $filename, $capsule, $seperator, $lineend, $extraFunctions));
    }
}
