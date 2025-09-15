<?php

namespace Stilmark\Base;

use Symfony\Component\Dotenv\Dotenv;

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

}