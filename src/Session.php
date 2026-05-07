<?php

namespace Stilmark\Base;

/**
 * Session management helper
 * Provides utilities for session lifecycle, timeouts, and security
 */
class Session
{
    /**
     * Initialize session with security settings
     * Should be called before session_start()
     * 
     * @param array $options Session configuration options
     * @return void
     */
    public static function configure(array $options = []): void
    {
        $defaults = [
            'cookie_httponly' => true,
            'cookie_secure' => false,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'name' => 'APP_SESSION',
            'gc_maxlifetime' => 43200, // 12 hours
        ];
        
        $config = array_merge($defaults, $options);
        
        ini_set('session.cookie_httponly', (string) (int) $config['cookie_httponly']);
        ini_set('session.cookie_secure', (string) (int) $config['cookie_secure']);
        ini_set('session.cookie_samesite', $config['cookie_samesite']);
        ini_set('session.use_strict_mode', (string) (int) $config['use_strict_mode']);
        ini_set('session.name', $config['name']);
        ini_set('session.gc_maxlifetime', (string) (int) $config['gc_maxlifetime']);
    }
    
    /**
     * Regenerate session ID (prevents session fixation)
     * 
     * @param bool $deleteOldSession Delete old session file
     * @return bool Success
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }
    
    /**
     * Destroy session completely
     * Clears session data, deletes cookie, and destroys session file
     * 
     * @return bool Success
     */
    public static function destroy(): bool
    {
        // Clear session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destroy session file
        return session_destroy();
    }
    
    /**
     * Check if session has expired based on idle timeout
     * 
     * @param string $lastActivityKey Session key storing last activity timestamp
     * @param int $idleTimeout Idle timeout in seconds
     * @return bool True if expired, false otherwise
     */
    public static function isIdleExpired(string $lastActivityKey = 'last_activity', int $idleTimeout = 43200): bool
    {
        if (!isset($_SESSION[$lastActivityKey])) {
            return true;
        }
        
        return (time() - $_SESSION[$lastActivityKey]) > $idleTimeout;
    }
    
    /**
     * Check if session has expired based on absolute timeout
     * 
     * @param string $loginTimeKey Session key storing login timestamp
     * @param int $absoluteTimeout Absolute timeout in seconds
     * @return bool True if expired, false otherwise
     */
    public static function isAbsoluteExpired(string $loginTimeKey = 'login_time', int $absoluteTimeout = 86400): bool
    {
        if (!isset($_SESSION[$loginTimeKey])) {
            return true;
        }
        
        return (time() - $_SESSION[$loginTimeKey]) > $absoluteTimeout;
    }
    
    /**
     * Update last activity timestamp (for rolling idle timeout)
     * 
     * @param string $lastActivityKey Session key to store timestamp
     * @return void
     */
    public static function updateActivity(string $lastActivityKey = 'last_activity'): void
    {
        $_SESSION[$lastActivityKey] = time();
    }
    
    /**
     * Set login timestamp (for absolute timeout)
     * 
     * @param string $loginTimeKey Session key to store timestamp
     * @return void
     */
    public static function setLoginTime(string $loginTimeKey = 'login_time'): void
    {
        $_SESSION[$loginTimeKey] = time();
    }
    
    /**
     * Get session value
     * Supports dot notation for nested arrays: 'user.company_id'
     * 
     * @param string $key Session key (supports dot notation)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Handle dot notation
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $_SESSION;
            
            foreach ($keys as $k) {
                if (!is_array($value) || !array_key_exists($k, $value)) {
                    return $default;
                }
                $value = $value[$k];
            }
            
            return $value;
        }
        
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session value
     * Supports dot notation for nested arrays: 'user.company_id'
     * 
     * @param string $key Session key (supports dot notation)
     * @param mixed $value Value to store
     * @return void
     */
    public static function set(string $key, $value): void
    {
        // Handle dot notation
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $target = &$_SESSION;
            
            // Navigate to the parent of the final key
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $k = $keys[$i];
                if (!isset($target[$k]) || !is_array($target[$k])) {
                    $target[$k] = [];
                }
                $target = &$target[$k];
            }
            
            $target[end($keys)] = $value;
            return;
        }
        
        $_SESSION[$key] = $value;
    }
    
    /**
     * Check if session key exists
     * Supports dot notation for nested arrays: 'user.company_id'
     * 
     * @param string $key Session key (supports dot notation)
     * @return bool
     */
    public static function has(string $key): bool
    {
        // Handle dot notation
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $parent = $_SESSION;
            $finalKey = end($keys);
            $parentKeys = array_slice($keys, 0, -1);
            
            // Navigate to parent of final key
            foreach ($parentKeys as $k) {
                if (!is_array($parent) || !array_key_exists($k, $parent)) {
                    return false;
                }
                $parent = $parent[$k];
            }
            
            // Check if final key exists and is not null (matching isset() behavior)
            return is_array($parent) && isset($parent[$finalKey]);
        }
        
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     * Supports dot notation for nested arrays: 'user.company_id'
     * 
     * @param string $key Session key (supports dot notation)
     * @return void
     */
    public static function remove(string $key): void
    {
        // Handle dot notation
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $target = &$_SESSION;
            
            // Navigate to the parent of the final key
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $k = $keys[$i];
                if (!is_array($target) || !array_key_exists($k, $target)) {
                    return; // Path doesn't exist, nothing to remove
                }
                $target = &$target[$k];
            }
            
            unset($target[end($keys)]);
            return;
        }
        
        unset($_SESSION[$key]);
    }
    
    /**
     * Get all session variables
     * 
     * @return array All session data
     */
    public static function all(): array
    {
        return $_SESSION ?? [];
    }
    
    /**
     * Flash data (set for next request only)
     * 
     * @param string $key Flash key
     * @param mixed $value Value to flash
     * @return void
     */
    public static function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get flash data and remove it
     * 
     * @param string $key Flash key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
