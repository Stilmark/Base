# Authentication (`Auth` + Middleware)

Base includes helpers for Google OAuth2 and request authentication.

### Google OAuth2

```php
use Stilmark\Base\Auth;

$auth = new Auth();
$auth->callout();  // Redirects to Google login
```

The callback endpoint should handle the token exchange:

```php
$user = $auth->callback($request);
```

### AuthMiddleware

```php
use Stilmark\Base\AuthMiddleware;

$middleware = new AuthMiddleware();
if (!$middleware->handle()) {
    http_response_code(401);
    exit('Unauthorized');
}
```

Implement `validateToken()` in `AuthMiddleware` for your application’s needs.
