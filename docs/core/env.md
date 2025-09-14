# Env

The `Env` class loads and manages environment variables from `.env` and `$_SERVER`/`$_ENV`.

## Overview
Call `Env::load()` early in your bootstrap to populate environment variables.

## Public API (typical)
```php
bool Env::load(string $path = null)
mixed Env::get(string $key, mixed $default = null)
void Env::set(string $key, string|int|bool|null $value)
```
> Signatures may vary slightly depending on your current code; adjust as needed.

## Usage
```php
use Stilmark\Base\Env;

Env::load(__DIR__ . '/../.env');
$tz = Env::get('APP_TIMEZONE', 'UTC');
Env::set('APP_DEBUG', true);
```
