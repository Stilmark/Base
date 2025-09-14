# Env

Environment variable management with `.env` file support and runtime configuration.

## Overview

The `Env` class provides a simple interface for managing environment variables in your application. It supports loading from `.env` files, runtime variable setting, and type-safe value retrieval with defaults.

## Class Reference

### Static Methods

#### `load(string $path): void`

Loads environment variables from a `.env` file.

**Parameters:**
- `$path` (string) - Path to the `.env` file

**Example:**
```php
use Stilmark\Base\Env;

Env::load(__DIR__ . '/.env');
Env::load('/path/to/custom.env');
```

#### `get(string $key, mixed $default = null): mixed`

Retrieves an environment variable value.

**Parameters:**
- `$key` (string) - Environment variable name
- `$default` (mixed) - Default value if variable doesn't exist

**Returns:** The environment variable value or default

**Example:**
```php
$apiUrl = Env::get('API_URL', 'https://api.default.com');
$debug = Env::get('APP_DEBUG', false);
$timeout = Env::get('API_TIMEOUT', 30);
```

#### `set(string $key, mixed $value): void`

Sets an environment variable at runtime.

**Parameters:**
- `$key` (string) - Environment variable name
- `$value` (mixed) - Value to set

**Example:**
```php
Env::set('FEATURE_FLAG', true);
Env::set('CACHE_TTL', 3600);
Env::set('API_VERSION', 'v2');
```

#### `has(string $key): bool`

Checks if an environment variable exists.

**Parameters:**
- `$key` (string) - Environment variable name

**Returns:** `true` if variable exists, `false` otherwise

**Example:**
```php
if (Env::has('GOOGLE_CLIENT_ID')) {
    // OAuth is configured
    $auth = new Auth();
}
```

#### `all(): array`

Returns all environment variables as an associative array.

**Returns:** Array of all environment variables

**Example:**
```php
$allVars = Env::all();
foreach ($allVars as $key => $value) {
    echo "$key = $value\n";
}
```

#### `clear(): void`

Clears all environment variables (useful for testing).

**Example:**
```php
// In test setup
Env::clear();
Env::set('APP_ENV', 'testing');
```

## Usage Patterns

### Application Bootstrap

```php
<?php
require 'vendor/autoload.php';

use Stilmark\Base\Env;

// Load environment configuration
Env::load(__DIR__ . '/.env');

// Set runtime configurations
date_default_timezone_set(Env::get('APP_TIMEZONE', 'UTC'));

// Configure error reporting based on environment
if (Env::get('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

### Configuration Classes

```php
class DatabaseConfig
{
    public static function getConnection(): array
    {
        return [
            'host' => Env::get('DB_HOST', 'localhost'),
            'database' => Env::get('DB_DATABASE', 'app'),
            'username' => Env::get('DB_USERNAME', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'port' => (int) Env::get('DB_PORT', 3306),
        ];
    }
}
```

### Feature Flags

```php
class FeatureFlags
{
    public static function isEnabled(string $feature): bool
    {
        return Env::get("FEATURE_{$feature}", false) === 'true';
    }
}

// Usage
if (FeatureFlags::isEnabled('NEW_DASHBOARD')) {
    // Show new dashboard
}
```

### Environment-Specific Logic

```php
$environment = Env::get('APP_ENV', 'production');

switch ($environment) {
    case 'development':
        $logLevel = 'debug';
        $cacheEnabled = false;
        break;
    case 'testing':
        $logLevel = 'error';
        $cacheEnabled = false;
        break;
    case 'production':
        $logLevel = 'warning';
        $cacheEnabled = true;
        break;
}
```

## .env File Format

### Basic Syntax

```bash
# Application settings
APP_NAME=MyApp
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=secret

# External services
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# OAuth credentials
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

### Advanced Features

```bash
# Multiline values
PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7VJTUt9Us8cKB
-----END PRIVATE KEY-----"

# Variable interpolation (if supported)
BASE_URL=https://api.example.com
API_V1_URL=${BASE_URL}/v1
API_V2_URL=${BASE_URL}/v2

# Comments and empty lines
# This is a comment

FEATURE_X=enabled  # Inline comment
```

## Type Conversion

The `Env` class returns string values by default. Use type casting for other types:

```php
// Boolean conversion
$debug = Env::get('APP_DEBUG') === 'true';
$enabled = filter_var(Env::get('FEATURE_X'), FILTER_VALIDATE_BOOLEAN);

// Integer conversion
$port = (int) Env::get('DB_PORT', 3306);
$timeout = intval(Env::get('API_TIMEOUT', 30));

// Array conversion (comma-separated)
$allowedHosts = explode(',', Env::get('ALLOWED_HOSTS', 'localhost'));

// JSON conversion
$config = json_decode(Env::get('COMPLEX_CONFIG', '{}'), true);
```

## Security Considerations

### Sensitive Data

Never commit `.env` files containing sensitive data to version control:

```bash
# Add to .gitignore
.env
.env.local
.env.production
```

### Environment Validation

Validate required environment variables on application startup:

```php
class EnvValidator
{
    private static array $required = [
        'APP_ENV',
        'DB_HOST',
        'DB_DATABASE',
        'GOOGLE_CLIENT_ID',
    ];

    public static function validate(): void
    {
        $missing = [];
        
        foreach (self::$required as $key) {
            if (!Env::has($key)) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing required environment variables: ' . 
                implode(', ', $missing)
            );
        }
    }
}

// In bootstrap
EnvValidator::validate();
```

## Testing

### Test Environment Setup

```php
// tests/bootstrap.php
use Stilmark\Base\Env;

// Clear existing environment
Env::clear();

// Load test-specific environment
Env::load(__DIR__ . '/../.env.testing');

// Override for tests
Env::set('APP_ENV', 'testing');
Env::set('DB_DATABASE', 'test_database');
```

### Mocking Environment Variables

```php
class EnvTest extends TestCase
{
    protected function setUp(): void
    {
        Env::clear();
    }

    public function testDatabaseConfiguration()
    {
        Env::set('DB_HOST', 'test-host');
        Env::set('DB_PORT', '5432');
        
        $config = DatabaseConfig::getConnection();
        
        $this->assertEquals('test-host', $config['host']);
        $this->assertEquals(5432, $config['port']);
    }
}
```

## Best Practices

1. **Use descriptive variable names**: `GOOGLE_CLIENT_ID` instead of `GCI`
2. **Group related variables**: Use prefixes like `DB_`, `REDIS_`, `MAIL_`
3. **Provide sensible defaults**: Always specify defaults for non-critical settings
4. **Validate early**: Check required variables during application bootstrap
5. **Document variables**: Maintain an `.env.example` file with all variables
6. **Environment-specific files**: Use `.env.local`, `.env.production` for overrides
