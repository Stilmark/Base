<?php

namespace Stilmark\Base;

use Exception;

class AuthMiddleware
{
    private string $authSessionName;
    
    public function __construct()
    {
        $this->authSessionName = Env::get('AUTH_SESSION_NAME', 'auth');
    }
    /**
     * Check if the request is authenticated
     */
    public function handle(): bool
    {

        // Check for Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        // Extract the token from the header (format: Bearer <token>)
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // If no token is provided, check for a session
        if (empty($token) && isset($_SESSION[$this->authSessionName]['access_token'])) {
            // Check if token has expired
            if (isset($_SESSION[$this->authSessionName]['token_expires']) && time() >= $_SESSION[$this->authSessionName]['token_expires']) {
                // Token expired, clear session and deny access
                $this->clearAuthSession();
                return false;
            }
            
            // Token exists and is not expired
            return true;
        }

        // Validate the token (implement your token validation logic here)
        if ($this->validateToken($token)) {
            return true;
        }

        // If we get here, authentication failed
        return false;
    }

    /**
     * Clear authentication session data
     */
    private function clearAuthSession(): void
    {
        unset($_SESSION[$this->authSessionName]);
    }

    /**
     * Validate the JWT token
     * 
     * @param string|null $token JWT token to validate
     * @return bool True if token is valid, false otherwise
     */
    private function validateToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            // Validate the token and get the decoded data
            $decoded = Jwt::validate($token);
            
            // Store the decoded token in the session for later use
            $_SESSION[$this->authSessionName]['jwt'] = $decoded;
            
            return true;
        } catch (Exception $e) {
            // Log the error if needed
            error_log('JWT validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
