# Using Env in Code

Read configuration values anywhere with `Env`.

## .env
```
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=Europe/Copenhagen
API_URL=https://api.example.com
```

## Bootstrap
```php
use Stilmark\Base\Env;

Env::load(__DIR__ . '/../.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'UTC'));
```

## In a service or controller
```php
use Stilmark\Base\Env;

class ApiService
{
    public function baseUrl(): string
    {
        return Env::get('API_URL', 'https://api.local');
    }
}
```

## Overriding at runtime
```php
Env::set('FEATURE_X', true);
```

## Tip
Validate required keys on startup and fail fast if missing.
