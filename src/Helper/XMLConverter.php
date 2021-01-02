<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Helper;

class XMLConverter
{
    /**
     *
     * @param array|object $data
     * @param \SimpleXMLElement $parent
     * @return string
     */
    public static function toXML($data, $parent = null, $rootElement = '<root/>')
    {
        if (is_null($parent)) {
            $parent = new \SimpleXMLElement($rootElement);
        }

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                static::toXML($value, $parent->addChild($key));
            } else {
                $parent->addChild($key, $value);
            }
        }

        return $parent->asXML();
    }
}
