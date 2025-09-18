<?php

namespace Stilmark\Base;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class Jwt
{
    /**
     * Generate a JWT token
     * 
     * @param array $payload The data to include in the token
     * @param int $expireInSeconds Token expiration time in seconds (default: 1 hour)
     * @return string JWT token
     * @throws Exception If required JWT configuration is missing
     */
    public static function generate(array $payload, int $expireInSeconds = 3600): string
    {
        $secret = Env::get('JWT_SECRET');
        $issuer = Env::get('JWT_ISSUER');
        $algorithm = Env::get('JWT_ALGORITHM', 'HS256');
        
        if (!$secret) {
            throw new Exception('JWT_SECRET is not configured in .env');
        }
        if (!$issuer) {
            throw new Exception('JWT_ISSUER is not configured in .env');
        }
        
        $issuedAt = time();
        $expire = $issuedAt + $expireInSeconds;
        
        $defaultClaims = [
            'iat'  => $issuedAt,         // Issued at
            'iss'  => $issuer,           // Issuer
            'nbf'  => $issuedAt,         // Not before
            'exp'  => $expire,           // Expire time
        ];
        
        // Merge default claims with provided payload
        $tokenData = array_merge($defaultClaims, $payload);
        
        return JWT::encode($tokenData, $secret, $algorithm);
    }
    
    /**
     * Validate and decode a JWT token
     * 
     * @param string $token JWT token to validate
     * @return object Decoded token data
     * @throws Exception If token is invalid, expired or required configuration is missing
     */
    public static function validate(string $token): object
    {
        $secret = Env::get('JWT_SECRET');
        $algorithm = Env::get('JWT_ALGORITHM', 'HS256');
        
        if (!$secret) {
            throw new Exception('JWT_SECRET is not configured in .env');
        }
        
        try {
            return JWT::decode($token, new Key($secret, $algorithm));
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage());
        }
    }
}
