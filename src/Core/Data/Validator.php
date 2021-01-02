<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

class Validator
{
    /**
     * @param $value
     * @return bool
     */
    public static function checkRequired($value): bool
    {
        if (is_numeric($value) && $value === 0) {
            return true;
        }

        return (isset($value) && !empty($value));
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkNumber($value): bool
    {
        return is_numeric($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkInt($value): bool
    {
        return is_int($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkBIC($value): bool
    {
        $bic = trim(strtolower(str_replace(' ', '', $value)));
        if (preg_match('/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i', $bic)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $value
     * @return bool
     */
    public static function checkIBAN($value): bool
    {
        $iban = $value;
        $iban = strtolower(str_replace(' ', '', $iban));
        $Countries = array('al' => 28,'ad' => 24,'at' => 20,'az' => 28,'bh' => 22,'be' => 16,'ba' => 20,'br' => 29,'bg' => 22,'cr' => 21,'hr' => 21,'cy' => 28,'cz' => 24,'dk' => 18,'do' => 28,'ee' => 20,'fo' => 18,'fi' => 18,'fr' => 27,'ge' => 22,'de' => 22,'gi' => 23,'gr' => 27,'gl' => 18,'gt' => 28,'hu' => 28,'is' => 26,'ie' => 22,'il' => 23,'it' => 27,'jo' => 30,'kz' => 20,'kw' => 30,'lv' => 21,'lb' => 28,'li' => 21,'lt' => 20,'lu' => 20,'mk' => 19,'mt' => 31,'mr' => 27,'mu' => 30,'mc' => 27,'md' => 24,'me' => 22,'nl' => 18,'no' => 15,'pk' => 24,'ps' => 29,'pl' => 28,'pt' => 25,'qa' => 29,'ro' => 24,'sm' => 27,'sa' => 24,'rs' => 22,'sk' => 24,'si' => 19,'es' => 24,'se' => 24,'ch' => 21,'tn' => 24,'tr' => 26,'ae' => 23,'gb' => 22,'vg' => 24);
        $Chars = array('a' => 10,'b' => 11,'c' => 12,'d' => 13,'e' => 14,'f' => 15,'g' => 16,'h' => 17,'i' => 18,'j' => 19,'k' => 20,'l' => 21,'m' => 22,'n' => 23,'o' => 24,'p' => 25,'q' => 26,'r' => 27,'s' => 28,'t' => 29,'u' => 30,'v' => 31,'w' => 32,'x' => 33,'y' => 34,'z' => 35);
        if (!array_key_exists(substr($iban, 0, 2), $Countries)) {
            return false;
        }

        if (strlen($iban) == $Countries[substr($iban, 0, 2)]) {
            $MovedChar = substr($iban, 4) . substr($iban, 0, 4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";
            foreach ($MovedCharArray as $key => $value) {
                if (!is_numeric($MovedCharArray[$key])) {
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if (function_exists("bcmod")) {
                return bcmod($NewString, '97') == 1;
            } else {
                $x = $NewString;
                $y = "97";
                $take = 5;
                $mod = "";
                do {
                    $a = (int)$mod . substr($x, 0, $take);
                    $x = substr($x, $take);
                    $mod = $a % $y;
                } while (strlen($x));
                return (int)$mod == 1;
            }
        }
        return false;
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function checkRange($value, $min, $max): bool
    {
        if (is_string($value) && !ctype_digit($value)) {
            return false;
// contains non digit characters
        }
        if (!is_int((int) $value)) {
            return false;
// other non-integer value or exceeds PHP_MAX_INT
        }
        return ($value >= $min && $value <= $max);
    }

    /**
     * @param $value
     * @param int $min
     * @return bool
     */
    public static function checkMinValue($value, $min): bool
    {
        if (is_string($value) && !ctype_digit($value)) {
            return false;
// contains non digit characters
        }
        if (!is_int((int) $value)) {
            return false;
// other non-integer value or exceeds PHP_MAX_INT
        }
        return ($value >= $min);
    }

    /**
     * @param $value
     * @param int $max
     * @return bool
     */
    public static function checkMaxValue($value, $max): bool
    {
        if (is_string($value) && !ctype_digit($value)) {
            return false;
// contains non digit characters
        }
        if (!is_int((int) $value)) {
            return false;
// other non-integer value or exceeds PHP_MAX_INT
        }
        return ($value <= $max);
    }

    /**
     * @param $value
     * @param int $minLength
     * @return bool
     */
    public static function checkMinStrLength($value, $minLength): bool
    {
        //if(!is_string($value)) return false;
        return strlen($value) >= $minLength;
    }

    /**
     * @param $value
     * @param int $maxLength
     * @return bool
     */
    public static function checkMaxStrLength($value, $maxLength): bool
    {
        //if(!is_string($value)) return false;
        return strlen($value) <= $maxLength;
    }

    /**
     * @param $value
     * @param int $minLength
     * @param int $maxLength
     * @return bool
     */
    public static function checkStrLengthRange($value, $minLength, $maxLength): bool
    {
        return ( static::checkMinStrLength($value, $minLength) && static::checkMaxStrLength($value, $maxLength) );
    }

    /**
     * @param $value
     * @param $data
     * @return bool
     */
    public static function checkEqual($value, $data): bool
    {
        return (bool)($value == $data);
    }

    /**
     * @param $value
     * @param $data
     * @return bool
     */
    public static function checkNotEqual($value, $data): bool
    {
        return (bool)($value != $data);
    }

    /**
     * @param $value
     * @param array $data
     * @return bool
     */
    public static function checkInArray($value, array $data): bool
    {
        return (bool) in_array($value, $data);
    }

    /**
     * Check if the value is an array
     *
     * @param mixed $value
     * @return boolean
     */
    public static function checkIsArray($value): bool
    {
        return is_array($value);
    }

    /**
     * Check if the value is an object
     *
     * @param mixed $value
     * @return boolean
     */
    public static function checkIsObject($value): bool
    {
        return is_object($value);
    }

    /**
     * Check if the value is an array or object
     *
     * @param mixed $value
     * @return boolean
     */
    public static function checkIsObjectOrArray($value): bool
    {
        return (static::checkIsArray($value) ||  static::checkIsObject($value));
    }

    /**
     * Checks if the given value is a valid json string
     * by decoding it
     *
     * @param mixed $value
     * @return boolean
     */
    public static function checkIsValidJsonString($value): bool
    {
        try {
            $jsonObject = json_decode($value);
            if (is_object($jsonObject) || is_array($jsonObject)) {
                return true;
            }
        } catch (\Exception $ex) {
            return false;
        }
        return false;
    }

    /**
     * @param $value
     * @param callable $func
     * @return bool
     */
    public static function checkCustom($value, callable $func): bool
    {
        return (bool)call_user_func_array($func, array($value));
    }

    /**
     * Encodes the given (HTML) string using @see htmlspecialchars
     *
     * @param string $value
     * @return string
     */
    public static function filterEncodeHtml($value): string
    {
        return htmlspecialchars($value, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    }

    /**
     * Encodes the given (HTML) string using @strip_tags
     *
     * @param string $value
     * @param array $allowable_tags
     * @return string
     */
    public static function filterStripHtml($value, $allowable_tags = array()): string
    {
        return strip_tags($value, $allowable_tags);
    }
}
