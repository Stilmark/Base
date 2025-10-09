<?php

namespace Stilmark\Base;

/**
 * CSRF protection middleware
 * Validates CSRF tokens and Origin/Referer headers on unsafe HTTP methods
 */
class CsrfMiddleware
{
    protected array $allowedOrigins;
    protected string $csrfSessionKey;
    protected int $bucketSeconds;
    protected bool $allowPreviousBucket;
    
    public function __construct(
        array $allowedOrigins = [],
        string $csrfSessionKey = 'csrf_secret',
        int $bucketSeconds = 3600,
        bool $allowPreviousBucket = true
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->csrfSessionKey = $csrfSessionKey;
        $this->bucketSeconds = $bucketSeconds;
        $this->allowPreviousBucket = $allowPreviousBucket;
    }
    
    /**
     * Handle CSRF validation
     * 
     * @return bool True if valid, false otherwise
     */
    public function handle(): bool
    {
        $request = new Request();
        
        // Only validate unsafe methods
        if (!$request->isUnsafeMethod()) {
            return true;
        }
        
        // Validate Origin/Referer if allowed origins are configured
        if (!empty($this->allowedOrigins)) {
            if (!$request->validateOrigin($this->allowedOrigins)) {
                return false;
            }
        }
        
        // Validate CSRF token
        return $request->validateCsrfToken(
            $this->csrfSessionKey,
            $this->bucketSeconds,
            $this->allowPreviousBucket
        );
    }
}
