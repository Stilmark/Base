<?php

namespace Stilmark\Base;

use Symfony\Component\Dotenv\Dotenv;

final class Env
{
    public static function load(?string $path = null)
    {
        (new Dotenv())->usePutenv(true)->load($path);

        // Set environment and debug mode
        define('APP_ENV', strtolower(self::get('APP_ENV', 'production')));
        define('DEV', in_array(APP_ENV, ['dev', 'local', 'development']));
        
        // Set error reporting based on debug mode
        $debug = filter_var(self::get('APP_DEBUG', DEV), FILTER_VALIDATE_BOOLEAN);
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }

        // Set locales and timezone
        setlocale(LC_ALL, self::get('LOCALE') ?? 'en_US.UTF8');
        date_default_timezone_set(self::get('TIMEZONE') ?? 'Europe/Copenhagen');

        // Configure session settings if not already started
        if (session_status() === PHP_SESSION_NONE) {
            self::configureSession();
        }
    }

    /**
     * Configure PHP session settings from environment variables
     */
    private static function configureSession(): void
    {
        // Only set session name if not already set
        if (empty(session_name())) {
            session_name(self::get('SESSION_AUTH_NAME', 'auth'));
        }

        // Set session cookie parameters
        $secure = filter_var(self::get('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOLEAN);
        $httpOnly = filter_var(self::get('SESSION_HTTP_ONLY', true), FILTER_VALIDATE_BOOLEAN);
        $sameSite = self::get('SESSION_SAME_SITE', 'Lax');
        
        session_set_cookie_params([
            'lifetime' => (int) self::get('SESSION_LIFETIME', 120) * 60, // Convert to seconds
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => in_array(ucfirst(strtolower($sameSite)), ['Lax', 'Strict', 'None']) 
                ? ucfirst(strtolower($sameSite)) 
                : 'Lax'
        ]);

        // Set session save path if using file driver
        if (self::get('SESSION_DRIVER', 'file') === 'file') {
            $savePath = self::get('SESSION_SAVE_PATH', sys_get_temp_dir());
            if (!is_dir($savePath) && !mkdir($savePath, 0700, true) && !is_dir($savePath)) {
                throw new \RuntimeException(sprintf('Session save path "%s" could not be created', $savePath));
            }
            session_save_path($savePath);
        }
    }

    public static function get(string $key, $default = null)
    {
        $v = getenv($key);
        if ($v !== false) return $v;
        if (isset($_SERVER[$key])) return $_SERVER[$key];
        if (isset($_ENV[$key])) return $_ENV[$key];
        return $default;
    }

    public static function set(string $key, string $value): void {
        if (function_exists('apache_setenv')) {
            @apache_setenv($key, $value, true);
        }
        @putenv("$key=$value");
        $_SERVER[$key] = $value;
        $_ENV[$key]    = $value;
    }
}
