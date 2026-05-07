# Session

The `Session` class provides a static helper API for managing PHP sessions with security best practices, timeout management, and flash data support.

## Capabilities

- Configure session security settings (HttpOnly, Secure, SameSite)
- Regenerate session IDs to prevent fixation attacks
- Destroy sessions completely (data, cookie, and file)
- Check idle and absolute session timeouts
- Manage session data with get/set/has/remove helpers
- Flash data for one-time messages

## Public API

### Session Lifecycle

```php
// Configure session security settings (call before session_start())
Session::configure(array $options = []): void

// Regenerate session ID (prevents session fixation)
Session::regenerate(bool $deleteOldSession = true): bool

// Destroy session completely (clears data, cookie, and file)
Session::destroy(): bool
```

### Timeout Management

```php
// Check if session has expired based on idle timeout
Session::isIdleExpired(string $lastActivityKey = 'last_activity', int $idleTimeout = 43200): bool

// Check if session has expired based on absolute timeout
Session::isAbsoluteExpired(string $loginTimeKey = 'login_time', int $absoluteTimeout = 86400): bool

// Update last activity timestamp (for rolling idle timeout)
Session::updateActivity(string $lastActivityKey = 'last_activity'): void

// Set login timestamp (for absolute timeout)
Session::setLoginTime(string $loginTimeKey = 'login_time'): void
```

### Session Data Management

```php
// Get a session value (supports dot notation for nested arrays)
Session::get(string $key, $default = null)

// Set a session value (supports dot notation for nested arrays)
Session::set(string $key, $value): void

// Check if a session key exists (supports dot notation for nested arrays)
Session::has(string $key): bool

// Remove a session key (supports dot notation for nested arrays)
Session::remove(string $key): void

// Get all session variables
Session::all(): array
```

### Flash Data

```php
// Set flash data (available for next request only)
Session::flash(string $key, $value): void

// Get flash data and remove it
Session::getFlash(string $key, $default = null)
```

## Configuration Options

The `configure()` method accepts the following options:

```php
[
    'cookie_httponly' => true,      // Prevent JavaScript access to session cookie
    'cookie_secure' => false,       // Require HTTPS (set to true in production)
    'cookie_samesite' => 'Lax',     // CSRF protection ('Strict', 'Lax', or 'None')
    'use_strict_mode' => true,      // Reject uninitialized session IDs
    'name' => 'APP_SESSION',        // Session cookie name
    'gc_maxlifetime' => 43200,      // Session garbage collection (12 hours)
]
```

## Basic Example

```php
use Stilmark\Base\Session;

// Configure session before starting it
Session::configure([
    'cookie_secure' => true,        // HTTPS only
    'cookie_samesite' => 'Strict',  // Strict CSRF protection
    'name' => 'MY_APP_SESSION',
]);

session_start();

// Set login timestamp after successful authentication
Session::setLoginTime();
Session::updateActivity();

// Store user data
Session::set('user_id', 123);
Session::set('username', 'john_doe');

// Check if user is logged in
if (Session::has('user_id')) {
    $userId = Session::get('user_id');
}

// Access nested data with dot notation
Session::set('user.company_id', 456);
$companyId = Session::get('user.company_id');      // Returns 456
$theme = Session::get('user.settings.theme', 'light'); // Returns value or 'light' if not set

// Check nested key exists
if (Session::has('user.permissions.admin')) {
    // User has admin permission
}

// Remove nested key
Session::remove('user.temp_data');

// Flash a success message
Session::flash('success', 'Login successful!');

// On next request, retrieve and display the flash message
$message = Session::getFlash('success'); // Returns message and removes it
```

## Timeout Example

```php
use Stilmark\Base\Session;

session_start();

// Check if session has expired (12 hour idle, 24 hour absolute)
if (Session::isIdleExpired('last_activity', 43200)) {
    Session::destroy();
    header('Location: /login?timeout=idle');
    exit;
}

if (Session::isAbsoluteExpired('login_time', 86400)) {
    Session::destroy();
    header('Location: /login?timeout=absolute');
    exit;
}

// Update activity timestamp on each request
Session::updateActivity();
```

## Security Best Practices

1. **Always configure before starting session**: Call `Session::configure()` before `session_start()`
2. **Use HTTPS in production**: Set `cookie_secure => true` when using HTTPS
3. **Regenerate on privilege escalation**: Call `Session::regenerate()` after login
4. **Implement timeouts**: Use idle and absolute timeouts to limit session lifetime
5. **Use SameSite cookies**: Set `cookie_samesite` to 'Lax' or 'Strict' for CSRF protection
