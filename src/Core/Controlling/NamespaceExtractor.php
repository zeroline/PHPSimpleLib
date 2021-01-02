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
    public static function byToken(string $src) : ?string
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
    public static function byRegExp(string $src) : ?string
    {
        if (preg_match('#^namespace\s+(.+?);#sm', $src, $m)) {
            return $m[1];
        }
        return null;
    }
}
