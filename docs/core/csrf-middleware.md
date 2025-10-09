# CsrfMiddleware

The `CsrfMiddleware` class provides automatic CSRF (Cross-Site Request Forgery) protection for unsafe HTTP methods (POST, PUT, PATCH, DELETE). It validates CSRF tokens and optionally checks Origin/Referer headers.

## Capabilities

- Automatic CSRF validation on unsafe HTTP methods
- Time-bucketed token validation with grace period
- Origin/Referer header validation
- Configurable session key and time buckets
- Safe methods (GET, HEAD, OPTIONS) automatically pass validation

## Constructor

```php
public function __construct(
    array $allowedOrigins = [],
    string $csrfSessionKey = 'csrf_secret',
    int $bucketSeconds = 3600,
    bool $allowPreviousBucket = true
)
```

### Parameters

- **`$allowedOrigins`**: Array of allowed origins (e.g., `['https://example.com']`). If empty, origin validation is skipped.
- **`$csrfSessionKey`**: Session key where CSRF secret is stored (default: `'csrf_secret'`)
- **`$bucketSeconds`**: Time bucket size in seconds for token rotation (default: `3600` = 1 hour)
- **`$allowPreviousBucket`**: Allow tokens from previous time bucket for grace period (default: `true`)

## Public API

```php
// Validate CSRF token and origin
handle(): bool
```

Returns `true` if the request passes CSRF validation, `false` otherwise.

## How It Works

1. **Safe methods bypass validation**: GET, HEAD, OPTIONS requests automatically pass
2. **Origin validation** (if configured): Checks `Origin` or `Referer` header against allowed origins
3. **Token validation**: Validates CSRF token from `X-CSRF-TOKEN` header or `_token` POST parameter
4. **Time-bucketed tokens**: Tokens are valid for current and previous time bucket (grace period)

## Basic Example

```php
use Stilmark\Base\CsrfMiddleware;
use Stilmark\Base\Render;

session_start();

// Create CSRF middleware with allowed origins
$csrfMiddleware = new CsrfMiddleware([
    'https://example.com',
    'https://www.example.com'
]);

// Validate CSRF on unsafe methods
if (!$csrfMiddleware->handle()) {
    Render::json(['error' => 'CSRF validation failed'], 403);
    exit;
}

// Continue with request handling
```

## Router Integration Example

```php
use Stilmark\Base\Router;
use Stilmark\Base\CsrfMiddleware;

$router = new Router();

// Create CSRF middleware
$csrfMiddleware = new CsrfMiddleware([
    'https://example.com'
]);

// Apply to specific routes
$router->post('/api/users', function($request) {
    // Handle user creation
}, [$csrfMiddleware]);

$router->delete('/api/users/:id', function($request, $id) {
    // Handle user deletion
}, [$csrfMiddleware]);
```

## Custom Configuration Example

```php
use Stilmark\Base\CsrfMiddleware;

// Custom time bucket (30 minutes) without grace period
$csrfMiddleware = new CsrfMiddleware(
    allowedOrigins: ['https://example.com'],
    csrfSessionKey: 'my_csrf_secret',
    bucketSeconds: 1800,           // 30 minutes
    allowPreviousBucket: false     // No grace period
);

if (!$csrfMiddleware->handle()) {
    http_response_code(403);
    echo 'CSRF validation failed';
    exit;
}
```

## Frontend Integration

### Generating Tokens

```php
use Stilmark\Base\Request;

session_start();

$request = new Request();
$csrfToken = $request->generateCsrfToken();

// Pass token to frontend
echo "<input type='hidden' name='_token' value='{$csrfToken}'>";
```

### Sending Tokens

**HTML Form:**
```html
<form method="POST" action="/api/users">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
    <input type="text" name="username">
    <button type="submit">Create User</button>
</form>
```

**JavaScript (Fetch API):**
```javascript
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ username: 'john_doe' })
});
```

## Security Notes

1. **Time-bucketed tokens**: Tokens rotate automatically every hour (default), reducing exposure window
2. **Grace period**: Previous bucket tokens are accepted to prevent race conditions during rotation
3. **Origin validation**: Additional layer of protection against cross-origin attacks
4. **Safe methods**: GET requests don't require CSRF tokens (as per HTTP specification)
5. **Session-based**: CSRF secret is stored in session, not exposed to client

## See Also

- [Request](request.md) - For generating CSRF tokens
- [AuthMiddleware](auth-middleware.md) - For authentication
- [Session](session.md) - For session management
