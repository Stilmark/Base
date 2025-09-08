<?php

namespace Stilmark\Base;

use Symfony\Component\Dotenv\Dotenv;

final class Env
{
    public static function load(string $path = null)
    {
        (new Dotenv())->usePutenv(true)->load($path);

        // Locales
        setlocale(LC_ALL, self::get('LOCALE') ?? 'en_US.UTF8');
        date_default_timezone_set(self::get('TIMEZONE') ?? 'Europe/Copenhagen');

        // Set DEV constant
        define('MODE', self::get('MODE'));
        define('DEV', in_array(MODE, ['DEV','LOCAL']));
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
