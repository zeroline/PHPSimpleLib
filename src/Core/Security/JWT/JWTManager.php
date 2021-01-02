<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Security\JWT;

use PHPSimpleLib\Core\Security\HMAC;

final class JWTManager
{
    /**
     *
     * @param string $input
     *
     * @return string
     */
    private static function base64Decode($input) : string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    
    /**
     *
     * @param string $input
     *
     * @return string
     */
    private static function base64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * Decodes a string with json_decode. The object is checked
     * within this function.
     *
     * @param string $input
     * @return mixed
     *
     * @throws \Exception
     */
    private static function jsonDecode(string $input)
    {
        $obj = json_decode($input);
        if (is_null($obj)) {
            throw new \Exception('Decoding object from json-input failed.');
        }
        return $obj;
    }
    
    /**
     * Encodes a object or array with json_encode. The result is
     * checked within this function.
     *
     * @param mixed $input
     * @return string
     *
     * @throws \Exception
     */
    private static function jsonEncode($input) : string
    {
        $json = json_encode($input);
        if ($json === false) {
            throw new \Exception('Creating json from input failed.');
        }
        return $json;
    }
    
    /**
     * Creates a JWT object from the given string.
     * All validity checks will be perfomed.
     * Failure results in exceptions.
     *
     * @param string $tokenString
     * @param string $key
     * @return JWT
     *
     * @throws \Exception
     */
    public static function jwtFromString(string $tokenString, string $key) : JWT
    {
        /* Seperate the string to build the token.  */
        $segments = explode('.', $tokenString);
        if (sizeof($segments) != 3) {
            throw new \Exception('Invalid number of segments in the given JWT string.');
        }
        
        list($encodedHeader, $encodedBody, $encodedSignature) = $segments;
        if (null === ($header = static::jsonDecode(static::base64Decode($encodedHeader)))) {
            throw new \Exception('Invalid header segment encoding');
        }
        if (null === $payload = static::jsonDecode(static::base64Decode($encodedBody))) {
            throw new \Exception('Invalid payload segment encoding');
        }

        if (empty($header->alg)) {
            throw new \Exception('Missing algorithm');
        }
        
        // Get and verify signature
        $signature = static::base64Decode($encodedSignature);
        
        if ($signature != HMAC::sign("$encodedHeader.$encodedBody", $key, $header->alg)) {
            throw new \Exception('Signature verification failed');
        }
        
        // Check not before timestamp
        if (isset($payload->nbf) && $payload->nbf > time()) {
            throw new \Exception('Token cannot be used before ' . date(\DateTime::ATOM, $payload->nbf));
        }
        
        // Check the issued at timestamp
        if (isset($payload->iat) && $payload->iat > time()) {
            throw new \Exception('Invalid issued at value ' . date(\DateTime::ATOM, $payload->iat));
        }
        
        // Check if the token is expired
        if (isset($payload->exp) && time() >= $payload->exp) {
            throw new \Exception('The given token is expired.');
        }
        
        return JWT::create($header->alg, $payload);
    }

    /**
     * Creates a base64 string from a given JWT object
     *
     * @param JWT $token
     * @param string $key
     * @return string
     */
    public static function stringFromJWT(JWT $token, string $key) : string
    {
        $header = array('alg' => $token->getAlgorithm(), 'typ' => $token->getType() );
        $segments = array();
        
        $segments[] = static::base64Encode(static::jsonEncode($header));
        $segments[] = static::base64Encode(static::jsonEncode($token->getPayload()->getData()));
        
        $signaturening_input = implode('.', $segments);
        $signaturenature = HMAC::sign($signaturening_input, $key, $token->getAlgorithm());
        $segments[] = static::base64Encode($signaturenature);
        
        return implode('.', $segments);
    }
    
    /**
     * Creates a new JWT
     *
     * @param mixed $payload
     * @param string $algorithm
     * @return JWT
     */
    public static function createJWTWithPayload($payload, string $algorithm = HMAC::ALGORITHM_HS256) : JWT
    {
        return JWT::create($algorithm, $payload);
    }
}
