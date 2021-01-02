<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Security;

final class HMAC
{
    public const ALGORITHM_HS256 = "HS256";
    public const ALGORITHM_HS384 = "HS384";
    public const ALGORITHM_HS512 = "HS512";
/**
     * Array indicating the supported algorithms
     *
     * @var array
     */
    private static $supportedAlgorithms = array(
        self::ALGORITHM_HS256 => "sha256",
        self::ALGORITHM_HS384 => "sha384",
        self::ALGORITHM_HS512 => "sha512"
    );
/**
     * Checks if the given algoritm is supported.
     *
     * @param string $algorithm
     * @return boolean
     */
    private static function isAlgorithmSupported(string $algorithm): bool
    {
        return array_key_exists($algorithm, static::$supportedAlgorithms);
    }

    /**
     * Returns a supported algorithm string
     *
     * @param string $algorithm
     * @return string|null
     */
    private static function getSupportedAlgorithm(string $algorithm): ?string
    {
        if (static::isAlgorithmSupported($algorithm)) {
            return static::$supportedAlgorithms[$algorithm];
        }
        return null;
    }

    /**
     * Signs a message with the given key and algorithm
     *
     * @param string $msg
     * @param string $key
     * @param string $method
     * @return string
     *
     * @throws \Exception
     */
    public static function sign(string $msg, string $key, string $method = self::ALGORITHM_HS256): string
    {
        $method = static::getSupportedAlgorithm($method);
        if (is_null($method)) {
            throw new \Exception('Algorithm not supported');
        }
        return hash_hmac($method, $msg, $key, true);
    }
}
