<?php

namespace PHPSimpleLib\Helper;

final class URI {
    /**
     * Parses an URI and returns its parts like parse_url
     *
     * @param string $uri
     * @return array
     */
    public static function parse(string $uri): array
    {
        // Normally a URI must be ASCII, however. However, often it's not and
        // parse_url might corrupt these strings.
        //
        // For that reason we take any non-ascii characters from the uri and
        // uriencode them first.
        $uri = preg_replace_callback(
            '/[^[:ascii:]]/u',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $uri
        );
    
        $result = [
            'scheme' => null,
            'host' => null,
            'port' => null,
            'user' => null,
            'path' => null,
            'fragment' => null,
            'query' => null,
        ];
    
        if (preg_match('% ^([A-Za-z][A-Za-z0-9+-\.]+): %x', $uri, $matches)) {
            $result['scheme'] = $matches[1];
            // Take what's left.
            $uri = substr($uri, strlen($result['scheme']) + 1);
        }
    
        // Taking off a fragment part
        if (false !== strpos($uri, '#')) {
            list($uri, $result['fragment']) = explode('#', $uri, 2);
        }
        // Taking off the query part
        if (false !== strpos($uri, '?')) {
            list($uri, $result['query']) = explode('?', $uri, 2);
        }
    
        if ('///' === substr($uri, 0, 3)) {
            // The triple slash uris are a bit unusual, but we have special handling
            // for them.
            $result['path'] = substr($uri, 2);
            $result['host'] = '';
        } elseif ('//' === substr($uri, 0, 2)) {
            // Uris that have an authority part.
            $regex = '
              %^
                //
                (?: (?<user> [^:@]+) (: (?<pass> [^@]+)) @)?
                (?<host> ( [^:/]* | \[ [^\]]+ \] ))
                (?: : (?<port> [0-9]+))?
                (?<path> / .*)?
              $%x
            ';
            if (!preg_match($regex, $uri, $matches)) {
                throw new \Exception('Invalid, or could not parse URI');
            }
            if ($matches['host']) {
                $result['host'] = $matches['host'];
            }
            if (isset($matches['port'])) {
                $result['port'] = (int) $matches['port'];
            }
            if (isset($matches['path'])) {
                $result['path'] = $matches['path'];
            }
            if ($matches['user']) {
                $result['user'] = $matches['user'];
            }
            if ($matches['pass']) {
                $result['pass'] = $matches['pass'];
            }
        } else {
            $result['path'] = $uri;
        }
    
        return $result;
    }
}