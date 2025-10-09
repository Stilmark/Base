# AuthMiddleware

The `AuthMiddleware` class provides generic authentication validation for routes, supporting both session-based and JWT token-based authentication. It includes automatic timeout management (idle and absolute) and is designed to be extended for custom validation logic.

## Capabilities

- Session-based authentication validation
- JWT Bearer token authentication support
- Idle timeout management (rolling timeout)
- Absolute timeout management (maximum session lifetime)
- Automatic activity timestamp updates
- Extensible validation for custom business logic

## Constructor

```php
public function __construct(
    string $authSessionKey = 'auth',
    int $idleTimeout = 43200,      // 12 hours
    int $absoluteTimeout = 86400    // 24 hours
)
```

### Parameters

- **`$authSessionKey`**: Session key where authentication data is stored (default: `'auth'`)
- **`$idleTimeout`**: Idle timeout in seconds (default: `43200` = 12 hours)
- **`$absoluteTimeout`**: Absolute timeout in seconds (default: `86400` = 24 hours)

## Public API

```php
// Validate authentication (session or JWT)
handle(): bool
```

Returns `true` if the request is authenticated, `false` otherwise.

## Protected Methods (For Extension)

```php
// Validate session data (override for custom validation)
protected function validateSession(array $sessionData): bool

// Clear authentication session data
protected function clearAuthSession(): void

// Validate JWT token
protected function validateToken(?string $token): bool
```

## How It Works

1. **JWT Token Check**: If an `Authorization: Bearer <token>` header is present, validates the JWT token
2. **Session Existence**: Checks if authentication session exists
3. **Idle Timeout**: Validates that the session hasn't been idle too long
4. **Absolute Timeout**: Validates that the session hasn't exceeded maximum lifetime
5. **Activity Update**: Updates the last activity timestamp (rolling timeout)
6. **Custom Validation**: Calls `validateSession()` for extensible validation logic

## Basic Example

```php
use Stilmark\Base\AuthMiddleware;
use Stilmark\Base\Render;

session_start();

$authMiddleware = new AuthMiddleware();

if (!$authMiddleware->handle()) {
    Render::json(['error' => 'Unauthorized'], 401);
    exit;
}

// User is authenticated, continue with request
```

## Custom Timeout Example

```php
use Stilmark\Base\AuthMiddleware;

// Custom timeouts: 30 min idle, 8 hours absolute
$authMiddleware = new AuthMiddleware(
    authSessionKey: 'auth',
    idleTimeout: 1800,      // 30 minutes
    absoluteTimeout: 28800  // 8 hours
);

if (!$authMiddleware->handle()) {
    http_response_code(401);
    echo 'Session expired';
    exit;
}
```

## Router Integration Example

```php
use Stilmark\Base\Router;
use Stilmark\Base\AuthMiddleware;

$router = new Router();
$authMiddleware = new AuthMiddleware();

// Protect specific routes
$router->get('/dashboard', function($request) {
    return ['message' => 'Welcome to dashboard'];
}, [$authMiddleware]);

$router->post('/api/users', function($request) {
    // Handle user creation
}, [$authMiddleware]);
```

## Extending for Custom Validation

Projects can extend `AuthMiddleware` to add custom validation logic:

```php
use Stilmark\Base\AuthMiddleware;

class MyAuthMiddleware extends AuthMiddleware
{
    private $db;
    
    public function __construct($db)
    {
        parent::__construct();
        $this->db = $db;
    }
    
    /**
     * Override to add custom validation
     * For example: check if user still exists and is active
     */
    protected function validateSession(array $sessionData): bool
    {
        // First, call parent validation
        if (!parent::validateSession($sessionData)) {
            return false;
        }
        
        // Custom validation: check if user exists and is active
        $userId = $sessionData['user']['id'] ?? null;
        if (!$userId) {
            return false;
        }
        
        $user = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user || $user['status'] !== 'active') {
            $this->clearAuthSession();
            return false;
        }
        
        return true;
    }
}

// Usage
$authMiddleware = new MyAuthMiddleware($db);
if (!$authMiddleware->handle()) {
    http_response_code(401);
    exit('Unauthorized');
}
```

## Timeout Behavior

### Idle Timeout (Rolling)
- Resets on every authenticated request
- User is logged out if inactive for the specified duration
- Default: 12 hours

### Absolute Timeout (Fixed)
- Does NOT reset on activity
- User is logged out after the specified duration from login, regardless of activity
- Default: 24 hours

### Example Timeline
```
Login at 10:00 AM
- Idle timeout: 12 hours (expires at 10:00 PM if no activity)
- Absolute timeout: 24 hours (expires at 10:00 AM next day, regardless of activity)

Activity at 9:00 PM
- Idle timeout: resets to 9:00 AM next day
- Absolute timeout: still expires at 10:00 AM next day
```

## Security Notes

1. **Automatic timeout enforcement**: Both idle and absolute timeouts are checked on every request
2. **Activity tracking**: Last activity timestamp is automatically updated on successful validation
3. **Session cleanup**: Expired sessions are automatically cleared
4. **JWT support**: Bearer tokens are validated using the `Jwt` class
5. **Extensible**: Override `validateSession()` to add custom business logic validation

## See Also

- [Auth](auth.md) - For OAuth2 authentication
- [Session](session.md) - For session management utilities
- [JWT](jwt.md) - For JWT token handling
- [Middleware Example](../examples/middleware.md) - Route protection examples
