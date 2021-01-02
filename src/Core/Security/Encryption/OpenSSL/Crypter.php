<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Security\Encryption\OpenSSL;

/**
 * OpenSSL Crypter class for easy use to en- and decrypt strings.
 * Please have a look at the functions documentation how the methods are used
 * and how an IV is used.
 * Note that the encrypt and decrypt functions use the parameter "password". This is a real password,
 * a key matching the ciphers requirements will be generated within the functions.
 *
 * Encrypted string is base64 encoded and always has the following format:
 * [IV][CIPHERTEXT][KEYSALT]
 *
 * Use only the given decrypt method to decrypt the data even if standard openSSL functions are used.
 * The decrypt function extracts the non secret parts from the encrypted string and performs decryption on
 * the cipher text.
 */
final class Crypter
{
    /**
     * String concat delimiter
     */
    private const PACKING_DELIMITER = '.';
    private const REGEX_AES_BIT_LENGTH = "/^aes-?([0-9]+)/i";
/**
     * Default amount of bytes used for the random bytes function
     */
    private const PBKDF_SALT_BYTES = 12;
/**
     * Amount of iterations for the "Password-Based Key Derivation Function 2"
     */
    private const PBKDF_ITERATIONS = 10000;
/**
     * Default key size for password drivation
     */
    private const DEFAULT_KEY_SIZE = 16;
/**
     * Prefered cipher string
     *
     * @var string
     */
    public const PREFERRED_CIPHER = "aes-256-gcm";
//"AES-256-CBC";

    /**
     * Tries to calculate a matching key size for the given cipher method.
     *
     * @param string $cipher
     * @return integer
     */
    private static function calculateKeyLength(string $cipher = self::PREFERRED_CIPHER): int
    {
        $keySize = self::DEFAULT_KEY_SIZE;
        if (preg_match(self::REGEX_AES_BIT_LENGTH, strtolower($cipher), $matches)) {
            $keySize = $matches[1] / 8;
        } else {
            $ivSize = openssl_cipher_iv_length($cipher);
            if ($ivSize > 0) {
                $keySize = $ivSize * 2;
            }
        }
        return $keySize;
    }

    /**
     * Generates a useful key string from the given password,
     * matching the target cipher key length requirements.
     *
     * @param string $password
     * @param string $cipher
     * @return array
     */
    private static function generateKeyAndSalt(string $password, string $cipher = self::PREFERRED_CIPHER): array
    {
        $salt = openssl_random_pseudo_bytes(self::PBKDF_SALT_BYTES);
        $keyLength = static::calculateKeyLength($cipher);
        $iterations = self::PBKDF_ITERATIONS;
        return array(openssl_pbkdf2($password, $salt, $keyLength, $iterations), $salt);
    }

    /**
     * Generates a key with the given salt.
     * Used to recover a key from a password and a stored
     * salt.
     *
     * @param string $password
     * @param string $salt
     * @param string $cipher
     * @return string
     */
    private static function generateKeyWithExistingSalt(string $password, string $salt, string $cipher = self::PREFERRED_CIPHER): string
    {
        $keyLength = static::calculateKeyLength($cipher);
        $iterations = self::PBKDF_ITERATIONS;
        return openssl_pbkdf2($password, $salt, $keyLength, $iterations);
    }

    /**
     * Concat the ciphered elements
     *
     * @param string $cipherText
     * @param string $iv
     * @param string $keySalt
     * @param string $tag
     * @return string
     */
    private static function packCipherElementsToString(string $cipherText, string $iv, string $keySalt, string $tag): string
    {
        $result = implode(self::PACKING_DELIMITER, array(base64_encode($cipherText), base64_encode($iv), base64_encode($keySalt), base64_encode($tag)));
        return $result;
    }

    /**
     * UnConcat the ciphered elements
     *
     * @param string $text
     * @return Object
     */
    private static function unpackCipherElementsFromString(string $text): object
    {
        list($cipherText, $iv, $keySalt, $tag) = explode(self::PACKING_DELIMITER, $text);
        $obj = new \stdClass();
        $obj->cipherText = base64_decode($cipherText);
        $obj->iv = base64_decode($iv);
        $obj->keySalt = base64_decode($keySalt);
        $obj->tag = base64_decode($tag);
        return $obj;
    }

    /**
     * Encrypts the given text with the password / key and
     * the given cipher method.
     * This class uses a preferred cipher if no other cipher is declared.
     * The IV will be generated randomly for every encryiption call.
     * The IV will be concatenated to the cipher text. It is not ment to be
     * a secret! The whole string will be base64 encoded.
     *
     * @param string $plainText
     * @param string $password
     * @param string $cipher
     * @return string
     */
    public static function encrypt(string $plainText, string $password, string $cipher = self::PREFERRED_CIPHER): string
    {
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            throw new \Exception('Cipher "' . $cipher . '" is not supported.');
        }

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        list($key, $keySalt) = static::generateKeyAndSalt($password, $cipher);
        $cipherText = openssl_encrypt($plainText, $cipher, $key, 0, $iv, $tag);
        return static::packCipherElementsToString($cipherText, $iv, $keySalt, $tag);
    }

    /**
     * Decrypts the given text with the password / key and
     * the given cipher method.
     * This class uses a preferred cipher if no other cipher is declared.
     * The IV will be extracted from the cipher text, concatenated with the encrypt
     * method.
     * It is not ment to be a secret!
     * The whole cipher text string must be base64 encoded.
     *
     * @param string $cipherText
     * @param string $password
     * @param string $cipher
     * @return string
     */
    public static function decrypt(string $cipherText, string $password, string $cipher = self::PREFERRED_CIPHER): string
    {
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            throw new \Exception('Cipher "' . $cipher . '" is not supported.');
        }

        $cipherElements = static::unpackCipherElementsFromString($cipherText);
        $key = static::generateKeyWithExistingSalt($password, $cipherElements->keySalt, $cipher);
        $plainText = openssl_decrypt($cipherElements->cipherText, $cipher, $key, 0, $cipherElements->iv, $cipherElements->tag);
        return $plainText;
    }
}
