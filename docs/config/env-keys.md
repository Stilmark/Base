# Environment Keys

The `Env` class loads configuration values from your `.env` file. Below are the key environment variables used by Stilmark Base.

## Base Configuration

Core configuration variables that control Base framework behavior:

```bash
# Base config
MODE=LOCAL
SERVER_NAME=base.dev
AUTH_SESSION_NAME=auth
CONTROLLER_NS=BaseApp\Controller\\
ROUTES_PATH=/app/routes.php
ROUTES_CACHE_PATH=/cache/routes.cache.php
```

### Base Config Variables

| Variable | Description | Default | Example |
|----------|-------------|---------|---------|
| `MODE` | Application mode (LOCAL, DEVELOPMENT, PRODUCTION) | `LOCAL` | `PRODUCTION` |
| `SERVER_NAME` | Server hostname for the application | `base.dev` | `myapp.com` |
| `AUTH_SESSION_NAME` | Session name for authentication | `auth` | `myapp_auth` |
| `CONTROLLER_NS` | Namespace for controllers | `BaseApp\Controller\\` | `App\Controllers\\` |
| `ROUTES_PATH` | Path to routes configuration file | `/app/routes.php` | `/config/routes.php` |
| `ROUTES_CACHE_PATH` | Path for cached routes | `/cache/routes.cache.php` | `/tmp/routes.cache` |

## Geolocation & Localization

```bash
# Geolocation
LOCALE=en_US.UTF8
TIMEZONE=Europe/Copenhagen
TIME_STANDARD=CET
```

### Localization Variables

| Variable | Description | Default | Example |
|----------|-------------|---------|---------|
| `LOCALE` | System locale setting | `en_US.UTF8` | `da_DK.UTF8` |
| `TIMEZONE` | Default timezone | `Europe/Copenhagen` | `America/New_York` |
| `TIME_STANDARD` | Time standard abbreviation | `CET` | `EST` |

## Database Configuration

```bash
# Database
DB_HOST=localhost
DB_DATABASE=baseapp
DB_USERNAME=local
DB_PASSWORD=local
```

### Database Variables

| Variable | Description | Default | Example |
|----------|-------------|---------|---------|
| `DB_HOST` | Database server hostname | `localhost` | `db.example.com` |
| `DB_DATABASE` | Database name | `baseapp` | `myapp_production` |
| `DB_USERNAME` | Database username | `local` | `app_user` |
| `DB_PASSWORD` | Database password | `local` | `secure_password` |

## Authentication

Google OAuth2 configuration for authentication:

```bash
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=/auth/google/callback
```

### OAuth Variables

| Variable | Description | Required | Example |
|----------|-------------|----------|---------|
| `GOOGLE_CLIENT_ID` | Google OAuth2 client ID | Yes | `123456789-abc.apps.googleusercontent.com` |
| `GOOGLE_CLIENT_SECRET` | Google OAuth2 client secret | Yes | `GOCSPX-abcdefghijklmnop` |
| `GOOGLE_REDIRECT_URI` | OAuth callback URI | Yes | `https://myapp.com/auth/google/callback` |

## Environment-Specific Configuration

### Development Environment

```bash
MODE=DEVELOPMENT
SERVER_NAME=localhost:8000
DB_DATABASE=baseapp_dev
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Production Environment

```bash
MODE=PRODUCTION
SERVER_NAME=myapp.com
DB_DATABASE=baseapp_production
GOOGLE_REDIRECT_URI=https://myapp.com/auth/google/callback
```

### Testing Environment

```bash
MODE=TESTING
SERVER_NAME=test.local
DB_DATABASE=baseapp_test
AUTH_SESSION_NAME=test_auth
```

## Custom Application Variables

You can define your own keys in `.env` for application-specific configuration:

```bash
# API Configuration
API_URL=https://api.example.com
API_TIMEOUT=30
API_KEY=your_api_key

# Feature Flags
FEATURE_NEW_UI=true
FEATURE_BETA_ACCESS=false

# Cache Settings
CACHE_DRIVER=redis
CACHE_TTL=3600

# Mail Configuration
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=mail_password
```

## Accessing Variables in Code

```php
use Stilmark\Base\Env;

// Base configuration
$mode = Env::get('MODE', 'LOCAL');
$serverName = Env::get('SERVER_NAME', 'localhost');
$controllerNs = Env::get('CONTROLLER_NS', 'App\\Controllers\\');

// Database configuration
$dbConfig = [
    'host' => Env::get('DB_HOST', 'localhost'),
    'database' => Env::get('DB_DATABASE', 'baseapp'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
];

// Custom variables
$apiUrl = Env::get('API_URL', 'https://api.default.com');
$featureEnabled = Env::get('FEATURE_NEW_UI') === 'true';
```

## Environment Validation

Validate required environment variables on application startup:

```php
class EnvValidator
{
    private static array $required = [
        'MODE',
        'SERVER_NAME',
        'DB_HOST',
        'DB_DATABASE',
        'DB_USERNAME',
    ];

    private static array $requiredForAuth = [
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
        'GOOGLE_REDIRECT_URI',
    ];

    public static function validate(): void
    {
        $missing = [];
        
        foreach (self::$required as $key) {
            if (!Env::has($key)) {
                $missing[] = $key;
            }
        }
        
        // Check auth variables if authentication is enabled
        if (Env::get('ENABLE_AUTH', 'false') === 'true') {
            foreach (self::$requiredForAuth as $key) {
                if (!Env::has($key)) {
                    $missing[] = $key;
                }
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
```

## Security Best Practices

1. **Never commit `.env` files** to version control
2. **Use strong passwords** for database and API keys
3. **Rotate secrets regularly** in production environments
4. **Use environment-specific files** (`.env.local`, `.env.production`)
5. **Validate required variables** during application bootstrap
6. **Use HTTPS** for OAuth redirect URIs in production
