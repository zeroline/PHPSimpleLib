<?php

use PHPUnit\Framework\TestCase;

use PHPSimpleLib\Core\Security\JWT\JWTManager;
use PHPSimpleLib\Core\Security\JWT\JWT;

final class JWTTest extends TestCase
{
    /**
     * Test variable for a secret string
     *
     * @var string
     */
    private $testSecret = "secret";

    /**
     * A base64 JWT string for testing
     *
     * @var string
     */
    private $testJWTCode = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.XbPfbIHMI6arZ3Y922BhjWgQzWXcXNrz0ogtVhfEd2o";

    private $testName = "John Doe";
    private $testIAT = 1516239022;
    private $testSub = "1234567890";
    private $testAlgo = "HS256";
    private $testType = "JWT";

    protected function setUp() : void
    {
    }

    public function testJWTClassFromString()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertInstanceOf(
            JWT::class,
            $token
        );
    }

    public function testJWTThrowsExceptionFromStringWithFalseSecret()
    {
        $this->expectException(\Exception::class);
        $token = JWTManager::jwtFromString($this->testJWTCode, strrev($this->testSecret));
    }

    public function testJWTObjectStructureFromString()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertObjectHasAttribute('header', $token);
        $this->assertObjectHasAttribute('payload', $token);
    }

    public function testJWTObjectContentsFromString()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertEquals($token->sub, $this->testSub);
        $this->assertEquals($token->iat, $this->testIAT);
        $this->assertEquals($token->name, $this->testName);
    }

    public function testJWTAlgorithmFromString()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertEquals($token->getAlgorithm(), $this->testAlgo);
    }

    public function testJWTTypeFromString()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertEquals($token->getType(), $this->testType);
    }

    public function testJWTRoundTrip()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $string = JWTManager::stringFromJWT($token, $this->testSecret);
        $this->assertEquals($string, $this->testJWTCode);
    }

    public function testJWTCreationFromData()
    {
        $token = JWTManager::createJWTWithPayload((object)[
            "sub" => $this->testSub,
            "name" => $this->testName,
            "iat" => $this->testIAT
        ]);

        $this->assertInstanceOf(
            JWT::class,
            $token
        );
    }

    public function testCreatedJWTFromPayloadAgainstExistingCode()
    {
        $token = JWTManager::createJWTWithPayload((object)[
            "sub" => $this->testSub,
            "name" => $this->testName,
            "iat" => $this->testIAT
        ]);
        $string = JWTManager::stringFromJWT($token, $this->testSecret);
        $this->assertEquals($string, $this->testJWTCode);
    }

    public function testJWTObjectBuildInGetters()
    {
        $token = JWTManager::jwtFromString($this->testJWTCode, $this->testSecret);
        $this->assertNull($token->getNotBefore());
        $this->assertNull($token->getExpired());
        $this->assertNull($token->getIssuer());
        $this->assertNull($token->getAudience());
        $this->assertNull($token->getIdentifiedBy());

        $this->assertEquals($token->getIssuedAt(), $this->testIAT);
        $this->assertEquals($token->getSubject(), $this->testSub);

        $this->assertEquals($token->name, $this->testName);
    }
}
