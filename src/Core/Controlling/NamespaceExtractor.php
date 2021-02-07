<?php

namespace PHPSimpleLib\Core\Controlling;

final class NamespaceExtractor
{

    /**
     * Extracts the namespace by token generation
     *
     * @param string $src
     * @return string|null
     */
    public static function byToken(string $src): ?string
    {
        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        } else {
            return $namespace;
        }
    }

    /**
     * Extracts the namespace via regex
     *
     * @param string $src
     * @return string|null
     */
    public static function byRegExp(string $src): ?string
    {
        if (preg_match('#^namespace\s+(.+?);#sm', $src, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * 
     * @param string $filename 
     * @return array 
     */
    public static function byFile(string $filename) : array 
    {
        $fp = fopen($filename, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) {
                break;
            }

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }

            if(empty($namespace)) {
                $namespace = static::byRegExp($buffer);
            }
        }

        return array($namespace, $class);
    }
}