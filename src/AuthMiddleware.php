<?php

namespace Stilmark\Base;

use Exception;

/**
 * Generic authentication middleware
 * Validates session existence and timeouts
 * Projects should extend this class to add custom validation (e.g., user status checks)
 */
class AuthMiddleware
{
    protected string $authSessionKey;
    protected int $idleTimeout;
    protected int $absoluteTimeout;
    
    public function __construct(
        string $authSessionKey = 'auth',
        int $idleTimeout = 43200,      // 12 hours
        int $absoluteTimeout = 86400    // 24 hours
    ) {
        $this->authSessionKey = $authSessionKey;
        $this->idleTimeout = $idleTimeout;
        $this->absoluteTimeout = $absoluteTimeout;
    }
    
    /**
     * Check if the request is authenticated
     * Override this method in your project to add custom validation
     */
    public function handle(): bool
    {
        // Check for Authorization header (JWT support)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // If JWT token is provided, validate it
        if (!empty($token)) {
            return $this->validateToken($token);
        }

        // Otherwise, check session-based authentication
        if (!isset($_SESSION[$this->authSessionKey])) {
            return false;
        }
        
        // Check idle timeout
        if (Session::isIdleExpired('last_activity', $this->idleTimeout)) {
            $this->clearAuthSession();
            return false;
        }
        
        // Check absolute timeout
        if (Session::isAbsoluteExpired('login_time', $this->absoluteTimeout)) {
            $this->clearAuthSession();
            return false;
        }
        
        // Update last activity (rolling timeout)
        Session::updateActivity('last_activity');
        
        // Allow projects to add custom validation
        return $this->validateSession($_SESSION[$this->authSessionKey]);
    }
    
    /**
     * Validate session data
     * Override this method in your project to add custom validation
     * (e.g., check user status, verify user exists in database)
     * 
     * @param array $sessionData Session data to validate
     * @return bool True if valid, false otherwise
     */
    protected function validateSession(array $sessionData): bool
    {
        // Base implementation: just check if session has required data
        // Projects should override this to add custom validation
        return !empty($sessionData);
    }

    /**
     * Clear authentication session data
     */
    protected function clearAuthSession(): void
    {
        unset($_SESSION[$this->authSessionKey]);
    }

    /**
     * Validate the JWT token
     * 
     * @param string|null $token JWT token to validate
     * @return bool True if token is valid, false otherwise
     */
    protected function validateToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            // Validate the token and get the decoded data
            $decoded = Jwt::validate($token);
            
            // Store the decoded token in the session for later use
            $_SESSION[$this->authSessionKey]['jwt'] = $decoded;
            
            return true;
        } catch (Exception $e) {
            // Log the error if needed
            error_log('JWT validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
