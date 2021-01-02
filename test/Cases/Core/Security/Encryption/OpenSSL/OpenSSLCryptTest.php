<?php

use PHPUnit\Framework\TestCase;

use PHPSimpleLib\Core\Security\Encryption\OpenSSL\Crypter;

final class OpenSSLCryptTest extends TestCase
{
    private const KEY = "beb768412e2affbe434d30bb571e1578";
    private const FALSE_KEY = "cdc768412e2affbe434d30bb571e1578";
    private const MESSAGE = "Now that there is the Tec-9, a crappy spray gun from South Miami. This gun is advertised as the most popular gun in American crime. Do you believe that shit? It actually says that in the little book that comes with it: the most popular gun in American crime. Like they're actually proud of that shit. ";
    
    protected function setUp() : void
    {
    }

    public function testDefaultEncryption()
    {
        $cipherText = Crypter::encrypt(self::MESSAGE, self::KEY);

        $this->assertTrue(is_string($cipherText) && !empty($cipherText));
        $this->assertFalse($cipherText == self::MESSAGE);
    }

    public function testEncryptionAndFollowingDecryption()
    {
        $cipherText = Crypter::encrypt(self::MESSAGE, self::KEY);
        $plainText = Crypter::decrypt($cipherText, self::KEY);

        $this->assertTrue($plainText == self::MESSAGE);
    }

    public function testFalseDecryption()
    {
        $cipherText = Crypter::encrypt(self::MESSAGE, self::KEY);
        $plainText = Crypter::decrypt($cipherText, self::FALSE_KEY);

        $this->assertFalse($plainText == self::MESSAGE);
    }
}
