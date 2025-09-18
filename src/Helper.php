<?php

namespace Stilmark\Base;

use Symfony\Component\Dotenv\Dotenv;
use Exception;

final class Helper
{
    // String conversion
    
    public static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    public static function snakeToCamel(string $input): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    // Array key conversion

    public static function arrayKeysCamelToSnake(array $array): array
    {
        $converted = [];
        foreach ($array as $key => $value) {
            $newKey = self::camelToSnake($key);

            // Recursive conversion if value is an array
            if (is_array($value)) {
                $value = self::arrayKeysCamelToSnake($value);
            }

            $converted[$newKey] = $value;
        }
        return $converted;
    }

    // Cookie handling

    /**
     * Set a secure HTTP cookie
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param array $options Optional settings:
     *   - expires: int (timestamp, defaults to session cookie)
     *   - path: string (defaults to '/')
     *   - domain: string (defaults to current domain)
     *   - secure: bool (defaults to true in production)
     *   - httpOnly: bool (defaults to true)
     *   - sameSite: 'Lax'|'Strict'|'None' (defaults to 'Lax')
     * @return bool True if successful, false otherwise
     */
    public static function setCookie(
        string $name,
        string $value,
        array $options = []
    ): bool {
        $defaults = [
            'expires' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? '') === '443',
            'httpOnly' => true,
            'sameSite' => 'Lax'
        ];

        $options = array_merge($defaults, $options);

        // Convert expires from timestamp to seconds since epoch if needed
        if ($options['expires'] > 0) {
            $options['expires'] = time() + $options['expires'];
        }

        return setcookie(
            $name,
            $value,
            [
                'expires' => $options['expires'],
                'path' => $options['path'],
                'domain' => $options['domain'],
                'secure' => $options['secure'],
                'httponly' => $options['httpOnly'],
                'samesite' => $options['sameSite']
            ]
        );
    }

    /**
     * Set a secure JWT cookie
     * 
     * @param string $jwt The JWT token
     * @param array $options Same as setCookie() options plus:
     *   - name: string (defaults to 'jwt')
     *   - expires: int (defaults to 1 day in seconds)
     * @return bool True if successful, false otherwise
     */
    public static function setJwtCookie(
        string $jwt,
        array $options = []
    ): bool {
        $defaults = [
            'name' => 'jwt',
            'expires' => 86400, // 1 day
            'sameSite' => 'Lax'
        ];

        $options = array_merge($defaults, $options);
        $name = $options['name'];
        unset($options['name']);

        return self::setCookie($name, $jwt, $options);
    }

    /**
     * Get a cookie value
     * 
     * @param string $name Cookie name
     * @param mixed $default Default value if cookie doesn't exist
     * @return mixed Cookie value or default
     */
    public static function getCookie(string $name, $default = null)
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Delete a cookie
     * 
     * @param string $name Cookie name
     * @param array $options Must match the options used when setting the cookie
     * @return bool True if successful, false otherwise
     */
    public static function deleteCookie(string $name, array $options = []): bool
    {
        if (!isset($_COOKIE[$name])) {
            return false;
        }

        $options = array_merge([
            'expires' => time() - 3600, // Past time
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? '') === '443',
            'httpOnly' => true,
            'sameSite' => 'Lax'
        ], $options);

        unset($_COOKIE[$name]);
        
        return setcookie(
            $name,
            '',
            [
                'expires' => $options['expires'],
                'path' => $options['path'],
                'domain' => $options['domain'],
                'secure' => $options['secure'],
                'httponly' => $options['httpOnly'],
                'samesite' => $options['sameSite']
            ]
        );
    }
}