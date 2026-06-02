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

    /**
     * Convert string to camelCase (lowercase first letter)
     *
     * @param string $input The string to convert
     * @return string camelCase string
     */
    public static function toCamelCase(string $input): string
    {
        return lcfirst(self::snakeToCamel($input));
    }

    /**
     * Convert string to snake_case
     *
     * @param string $input The string to convert
     * @return string snake_case string
     */
    public static function toSnakeCase(string $input): string
    {
        return self::camelToSnake($input);
    }

    // JSON utilities

    /**
     * Safely decode JSON string to array
     *
     * @param mixed $json JSON string or null
     * @param array $default Default value if decode fails
     * @return array Decoded array or default
     */
    public static function jsonDecode($json, array $default = []): array
    {
        if (empty($json) || !is_string($json)) {
            return $default;
        }

        $decoded = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    /**
     * Encode array to JSON string
     *
     * @param mixed $data Data to encode
     * @param bool $pretty Pretty print output
     * @return string JSON string or empty string on failure
     */
    public static function jsonEncode($data, bool $pretty = false): string
    {
        $options = $pretty ? JSON_PRETTY_PRINT : 0;
        $json = json_encode($data, $options);
        return $json !== false ? $json : '';
    }

    /**
     * Check if string is valid JSON
     *
     * @param mixed $string String to check
     * @return bool True if valid JSON
     */
    public static function isJson($string): bool
    {
        if (empty($string) || !is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // Array utilities

    /**
     * Keep only specified keys from an array
     *
     * @param mixed $source Source array
     * @param array $keys Keys to keep
     * @return array Array with only specified keys, or empty array if source is not an array
     */
    public static function arrayOnly($source, array $keys): array
    {
        if (!is_array($source)) {
            return [];
        }
        return array_intersect_key($source, array_flip($keys));
    }

    /**
     * Remove specified keys from an array
     *
     * @param mixed $source Source array
     * @param array $keys Keys to remove
     * @return array Array without specified keys, or empty array if source is not an array
     */
    public static function arrayExcept($source, array $keys): array
    {
        if (!is_array($source)) {
            return [];
        }
        return array_diff_key($source, array_flip($keys));
    }

    /**
     * Get a value from nested array using dot notation
     *
     * @param mixed $array Source array
     * @param string $key Dot-notation key (e.g., 'user.profile.name')
     * @param mixed $default Default value if key not found
     * @return mixed The value or default
     */
    public static function arrayGet($array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!is_array($array) || !array_key_exists($k, $array)) {
                return $default;
            }
            $array = $array[$k];
        }
        return $array;
    }

    /**
     * Check if array has a value at dot-notation key
     *
     * @param mixed $array Source array
     * @param string $key Dot-notation key
     * @return bool True if key exists
     */
    public static function arrayHas($array, string $key): bool
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!is_array($array) || !array_key_exists($k, $array)) {
                return false;
            }
            $array = $array[$k];
        }
        return true;
    }

    /**
     * Find first element matching a callback
     *
     * @param mixed $array Source array
     * @param callable $callback Function that returns true for match
     * @param mixed $default Default value if not found
     * @return mixed First matching element or default
     */
    public static function arrayFind($array, callable $callback, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Find last element matching a callback
     *
     * @param mixed $array Source array
     * @param callable $callback Function that returns true for match
     * @param mixed $default Default value if not found
     * @return mixed Last matching element or default
     */
    public static function arrayFindLast($array, callable $callback, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }
        $result = $default;
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                $result = $value;
            }
        }
        return $result;
    }

    /**
     * Find all elements matching a callback (reindexed)
     *
     * @param mixed $array Source array
     * @param callable $callback Function that returns true for match
     * @return array All matching elements (reindexed)
     */
    public static function arrayFindAll($array, callable $callback): array
    {
        if (!is_array($array)) {
            return [];
        }
        return array_values(array_filter($array, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Find key of first element matching a callback
     *
     * @param mixed $array Source array
     * @param callable $callback Function that returns true for match
     * @return int|string|null Key of first match or null
     */
    public static function arrayFindKey($array, callable $callback)
    {
        if (!is_array($array)) {
            return null;
        }
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Extract a single column/property from each element
     *
     * @param mixed $array Source array
     * @param string $key Key to extract
     * @param string|null $indexKey Optional key to use as index
     * @return array Plucked values
     */
    public static function arrayPluck($array, string $key, ?string $indexKey = null): array
    {
        if (!is_array($array)) {
            return [];
        }
        return array_column($array, $key, $indexKey);
    }

    // Array key conversion

    /**
     * Recursively converts all array keys from camelCase to snake_case
     *
     * @param mixed $array Source array
     * @return array Array with snake_case keys, or empty array if input is not an array
     */
    public static function arrayKeysCamelToSnake($array): array
    {
        if (!is_array($array)) {
            return [];
        }

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