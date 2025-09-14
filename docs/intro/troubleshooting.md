# Troubleshooting

Common issues and solutions when working with Stilmark Base.

## Installation Issues

### Composer Installation Fails

**Problem:** `composer require stilmark/base` fails with dependency conflicts.

**Solution:**
```bash
# Update Composer first
composer self-update

# Clear Composer cache
composer clear-cache

# Try installation with verbose output
composer require stilmark/base -v
```

### PHP Version Compatibility

**Problem:** "Your PHP version (X.X.X) does not satisfy requirement php ^8.2"

**Solution:**
- Ensure you're running PHP 8.2 or higher
- Check your PHP version: `php -v`
- Update PHP through your system package manager or use a version manager like phpbrew

### Missing Extensions

**Problem:** Class 'Symfony\Component\Dotenv\Dotenv' not found or OAuth functionality not working.

**Solution:**
```bash
# Install required PHP extensions
sudo apt-get install php8.2-json php8.2-curl  # Ubuntu/Debian
brew install php@8.2                          # macOS with Homebrew

# Verify extensions are loaded
php -m | grep -E "(json|curl)"
```

## Runtime Issues

### Environment Variables Not Loading

**Problem:** `Env::get()` returns null or default values.

**Solutions:**
1. Check `.env` file exists and is readable
2. Verify file path in `Env::load()` call
3. Ensure no syntax errors in `.env` file

```php
// Debug environment loading
use Stilmark\Base\Env;

if (!file_exists('.env')) {
    echo "Error: .env file not found\n";
}

Env::load('.env');
var_dump(Env::get('APP_ENV')); // Should not be null
```

### Routing Issues

**Problem:** Routes not matching or 404 errors.

**Solutions:**
1. Check route definitions syntax
2. Verify controller namespace and method exist
3. Ensure proper URL rewriting (Apache/Nginx)

```php
// Debug routing
$router = new Router();
$router->addRoute('GET', '/debug', function() {
    return 'Route working!';
});
```

### OAuth Authentication Fails

**Problem:** Google OAuth returns errors or fails silently.

**Solutions:**
1. Verify Google OAuth credentials in `.env`
2. Check redirect URI matches Google Console settings
3. Ensure HTTPS in production

```bash
# Required .env variables
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
```

### Session Issues

**Problem:** Sessions not persisting or authentication middleware failing.

**Solutions:**
1. Ensure sessions are started before using Auth
2. Check session configuration
3. Verify session storage permissions

```php
// Start sessions before using Auth
session_start();

// Check session configuration
var_dump(session_get_cookie_params());
```

## Performance Issues

### Slow Route Resolution

**Problem:** Application responds slowly on route matching.

**Solutions:**
1. Enable route caching in production
2. Optimize route definitions order
3. Use FastRoute's caching features

```php
// Enable route caching
$router = new Router();
$router->setCacheFile('/path/to/cache/routes.cache');
```

### Memory Usage

**Problem:** High memory consumption.

**Solutions:**
1. Profile memory usage with Xdebug
2. Optimize large data processing
3. Use streaming for large responses

## Development Tips

### Debugging

Enable detailed error reporting during development:

```php
// In your bootstrap file
if (Env::get('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}
```

### Logging

Use the built-in Logger for debugging:

```php
use Stilmark\Base\Logger;

$logger = new Logger();
$logger->info('Debug message', ['context' => $data]);
```

### Testing Routes

Create a simple test script:

```php
<?php
require 'vendor/autoload.php';

use Stilmark\Base\Router;

$router = new Router();
$router->addRoute('GET', '/test', function() {
    return 'Test successful!';
});

// Simulate a request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test';

$router->dispatch();
```

## Getting Help

If you're still experiencing issues:

1. Check the [GitHub Issues](https://github.com/Stilmark/Base/issues)
2. Review the [complete documentation](https://stilmark-base.gitbook.io/base/)
3. Create a minimal reproduction case
4. Submit a new issue with:
   - PHP version
   - Stilmark Base version
   - Error messages
   - Minimal code example
