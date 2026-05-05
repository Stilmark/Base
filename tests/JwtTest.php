<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Jwt;
use Stilmark\Base\Env;
use Exception;

class JwtTest extends TestCase
{
    protected function setUp(): void
    {
        Env::set('JWT_SECRET', 'test-secret-key-for-unit-tests-minimum-length');
        Env::set('JWT_ISSUER', 'test-issuer');
        Env::set('JWT_ALGORITHM', 'HS256');
    }

    protected function tearDown(): void
    {
        putenv('JWT_SECRET');
        putenv('JWT_ISSUER');
        putenv('JWT_ALGORITHM');
        unset($_SERVER['JWT_SECRET'], $_SERVER['JWT_ISSUER'], $_SERVER['JWT_ALGORITHM']);
        unset($_ENV['JWT_SECRET'], $_ENV['JWT_ISSUER'], $_ENV['JWT_ALGORITHM']);
    }

    public function testGenerateReturnsString(): void
    {
        $token = Jwt::generate(['user_id' => 123]);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testGenerateCreatesValidJwtFormat(): void
    {
        $token = Jwt::generate(['user_id' => 123]);
        
        // JWT should have 3 parts separated by dots
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testValidateDecodesToken(): void
    {
        $token = Jwt::generate(['user_id' => 123, 'email' => 'test@example.com']);
        $decoded = Jwt::validate($token);
        
        $this->assertEquals(123, $decoded->user_id);
        $this->assertEquals('test@example.com', $decoded->email);
    }

    public function testValidateIncludesStandardClaims(): void
    {
        $token = Jwt::generate(['user_id' => 123]);
        $decoded = Jwt::validate($token);
        
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('iss', $decoded);
        $this->assertObjectHasProperty('nbf', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertEquals('test-issuer', $decoded->iss);
    }

    public function testGenerateWithCustomExpiration(): void
    {
        $token = Jwt::generate(['user_id' => 123], 7200); // 2 hours
        $decoded = Jwt::validate($token);
        
        $expectedExp = $decoded->iat + 7200;
        $this->assertEquals($expectedExp, $decoded->exp);
    }

    public function testGenerateThrowsExceptionWithoutSecret(): void
    {
        putenv('JWT_SECRET');
        unset($_SERVER['JWT_SECRET'], $_ENV['JWT_SECRET']);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JWT_SECRET is not configured');
        
        Jwt::generate(['user_id' => 123]);
    }

    public function testGenerateThrowsExceptionWithoutIssuer(): void
    {
        putenv('JWT_ISSUER');
        unset($_SERVER['JWT_ISSUER'], $_ENV['JWT_ISSUER']);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JWT_ISSUER is not configured');
        
        Jwt::generate(['user_id' => 123]);
    }

    public function testValidateThrowsExceptionWithoutSecret(): void
    {
        $token = Jwt::generate(['user_id' => 123]);
        
        putenv('JWT_SECRET');
        unset($_SERVER['JWT_SECRET'], $_ENV['JWT_SECRET']);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JWT_SECRET is not configured');
        
        Jwt::validate($token);
    }

    public function testValidateThrowsExceptionForInvalidToken(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid or expired token');
        
        Jwt::validate('invalid.token.here');
    }

    public function testValidateThrowsExceptionForTamperedToken(): void
    {
        $token = Jwt::generate(['user_id' => 123]);
        
        // Tamper with the token
        $parts = explode('.', $token);
        $parts[1] = base64_encode('{"user_id":999}');
        $tamperedToken = implode('.', $parts);
        
        $this->expectException(Exception::class);
        
        Jwt::validate($tamperedToken);
    }

    public function testPayloadMergesWithDefaultClaims(): void
    {
        $token = Jwt::generate([
            'user_id' => 123,
            'role' => 'admin',
            'permissions' => ['read', 'write']
        ]);
        
        $decoded = Jwt::validate($token);
        
        // Custom claims
        $this->assertEquals(123, $decoded->user_id);
        $this->assertEquals('admin', $decoded->role);
        $this->assertEquals(['read', 'write'], $decoded->permissions);
        
        // Default claims still present
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
    }
}
