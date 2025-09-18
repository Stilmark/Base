# JWT (JSON Web Tokens)

The `Jwt` class provides a simple interface for working with JSON Web Tokens (JWT) using the `firebase/php-jwt` library. It handles token generation and validation with support for standard JWT claims.

## Environment Variables

```env
# Required
JWT_SECRET=your-secret-key-here
JWT_ISSUER=https://your-domain.com

# Optional (defaults to HS256)
JWT_ALGORITHM=HS256
```

## Usage

### Generating a Token

```php
use Stilmark\Base\Jwt;

// Generate a token with custom claims
$token = Jwt::generate([
    'user_id' => 123,
    'email' => 'user@example.com',
    // Add any custom claims here
]);

// With custom expiration (in seconds)
$token = Jwt::generate(
    ['user_id' => 123],
    86400 // 24 hours
);
```

### Validating a Token

```php
use Stilmark\Base\Jwt;

try {
    $decoded = Jwt::validate($token);
    // Access token data
    $userId = $decoded->user_id;
    $email = $decoded->email;
} catch (Exception $e) {
    // Handle invalid token
    echo 'Token validation failed: ' . $e->getMessage();
}
```

### Using with AuthMiddleware

The `AuthMiddleware` automatically handles JWT validation from the `Authorization` header:

```php
use Stilmark\Base\AuthMiddleware;

$auth = new AuthMiddleware();
if (!$auth->handle()) {
    http_response_code(401);
    exit('Unauthorized');
}

// If we get here, the request is authenticated
// Access the decoded token from the session if needed
$decodedToken = $_SESSION[env('AUTH_SESSION_NAME', 'auth')]['jwt'];
```

## Security Considerations

1. **Keep the JWT_SECRET secure** - Never commit it to version control.
2. **Use HTTPS** - Always use HTTPS to prevent token interception.
3. **Token Expiration** - Always set a reasonable expiration time for tokens.
4. **Sensitive Data** - Avoid storing sensitive information in the token payload.
