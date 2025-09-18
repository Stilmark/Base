# JWT Authentication Examples

This guide demonstrates practical examples of using JWT (JSON Web Tokens) with the Stilmark Base library.

## Table of Contents
- [Basic Usage](#basic-usage)
- [Using with AuthMiddleware](#using-with-authmiddleware)
- [Handling Token Refresh](#handling-token-refresh)
- [Custom Claims and Validation](#custom-claims-and-validation)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

## Prerequisites

Make sure you have these environment variables configured in your `.env` file:

```env
JWT_SECRET=your-secret-key-here
JWT_ISSUER=https://your-domain.com
JWT_ALGORITHM=HS256
```

## Basic Usage

### 1. Generating a Token

```php
use Stilmark\Base\Jwt;

// Basic token with user ID and email
$token = Jwt::generate([
    'user_id' => 123,
    'email' => 'user@example.com'
]);

// With custom expiration (24 hours)
$token = Jwt::generate(
    ['user_id' => 123],
    86400  // 24 hours in seconds
);
```

### 2. Validating a Token

```php
try {
    $decoded = Jwt::validate($token);
    
    // Access token data
    $userId = $decoded->user_id;
    $email = $decoded->email;
    
    // Token expiration time
    $expiresAt = date('Y-m-d H:i:s', $decoded->exp);
    
} catch (Exception $e) {
    // Handle invalid token
    error_log('JWT validation failed: ' . $e->getMessage());
    http_response_code(401);
    exit('Invalid token');
}

### 3. Using Cookies for JWT Storage

For web applications, it's recommended to store JWTs in secure, HTTP-only cookies:

```php
use Stilmark\Base\Jwt;
use Stilmark\Base\Helper;

// After successful authentication
$token = Jwt::generate([
    'user_id' => 123,
    'email' => 'user@example.com'
]);

// Set secure, HTTP-only cookie (defaults to 1 day)
Helper::setJwtCookie($token, [
    'expires' => 86400,  // 1 day
    'sameSite' => 'Strict'  // Or 'Lax' if you need cross-site requests
]);

// In subsequent requests, get the JWT from the cookie
$jwt = Helper::getCookie('jwt');
if ($jwt) {
    try {
        $decoded = Jwt::validate($jwt);
        // User is authenticated
    } catch (Exception $e) {
        // Handle invalid token
        Helper::deleteCookie('jwt');
    }
}

// To log out (invalidate the token)
Helper::deleteCookie('jwt');
```

### 4. Complete Login Example with Cookies

```php
// Login endpoint
$router->post('/login', function($request) {
    // Validate credentials (pseudo-code)
    $user = User::validateCredentials(
        $request->input('email'),
        $request->input('password')
    );

    if (!$user) {
        http_response_code(401);
        return ['error' => 'Invalid credentials'];
    }

    // Generate JWT
    $token = Jwt::generate([
        'user_id' => $user->id,
        'email' => $user->email,
        'roles' => $user->roles
    ]);

    // Set secure cookie
    Helper::setJwtCookie($token, [
        'expires' => 86400,  // 1 day
        'sameSite' => 'Strict'
    ]);

    return [
        'status' => 'success',
        'user' => [
            'id' => $user->id,
            'email' => $user->email
        ]
    ];
});

// Logout endpoint
$router->post('/logout', function() {
    Helper::deleteCookie('jwt');
    return ['status' => 'success'];
});
```

## Using with AuthMiddleware

The `AuthMiddleware` can automatically validate JWT tokens from the `Authorization` header.

### 1. Protect a Route

```php
// In your route definition
$router->get('/protected-route', function() {
    return ['message' => 'This is a protected route'];
})->middleware([new AuthMiddleware()]);
```

### 2. Accessing User Data in Protected Routes

After successful validation, the decoded token is stored in the session:

```php
$router->get('/profile', function($request) {
    $decodedToken = $_SESSION[env('AUTH_SESSION_NAME', 'auth')]['jwt'] ?? null;
    
    if (!$decodedToken) {
        http_response_code(401);
        return ['error' => 'Not authenticated'];
    }
    
    return [
        'user_id' => $decodedToken->user_id,
        'email' => $decodedToken->email ?? null
    ];
});
```

## Handling Token Refresh

Implement a refresh token endpoint:

```php
$router->post('/refresh-token', function($request) {
    $refreshToken = $request->input('refresh_token');
    
    try {
        // Validate the refresh token (you might store these in a database)
        $decoded = Jwt::validate($refreshToken);
        
        // Issue a new access token
        $newToken = Jwt::generate([
            'user_id' => $decoded->user_id,
            'email' => $decoded->email
        ]);
        
        return [
            'access_token' => $newToken,
            'expires_in' => 3600  // 1 hour
        ];
        
    } catch (Exception $e) {
        http_response_code(401);
        return ['error' => 'Invalid refresh token'];
    }
});
```

## Custom Claims and Validation

### Adding Custom Claims

```php
$token = Jwt::generate([
    'user_id' => 123,
    'roles' => ['admin', 'editor'],
    'custom_data' => [
        'preferences' => ['theme' => 'dark'],
        'permissions' => ['read:users', 'write:posts']
    ]
]);
```

### Validating Custom Claims

```php
try {
    $decoded = Jwt::validate($token);
    
    // Check for required roles
    if (!in_array('admin', $decoded->roles ?? [])) {
        throw new Exception('Insufficient permissions');
    }
    
    // Access custom data
    $theme = $decoded->custom_data->preferences->theme ?? 'light';
    
} catch (Exception $e) {
    // Handle validation errors
}
```

## Error Handling

### Common JWT Exceptions

```php
try {
    $decoded = Jwt::validate($invalidToken);
} catch (Exception $e) {
    $error = $e->getMessage();
    
    // Handle specific error cases
    if (str_contains($error, 'expired')) {
        http_response_code(401);
        return ['error' => 'Token has expired', 'code' => 'token_expired'];
    } elseif (str_contains($error, 'signature')) {
        http_response_code(401);
        return ['error' => 'Invalid token signature', 'code' => 'invalid_signature'];
    } else {
        // Log other errors
        error_log("JWT Error: $error");
        http_response_code(500);
        return ['error' => 'Authentication error'];
    }
}
```

## Best Practices

1. **Secure Token Storage**
   - For web applications, always use HTTP-only, secure cookies with SameSite attribute
   - Set appropriate cookie expiration times (shorter is more secure)
   - For mobile/SPA, use secure storage (e.g., Keychain, SecureStore)
   - Never store sensitive data in JWT payload
   - Implement proper token refresh flow

2. **Token Expiration**
   - Keep access tokens short-lived (e.g., 15-60 minutes)
   - Use refresh tokens for longer sessions

3. **Secret Management**
   - Never hardcode JWT_SECRET in your code
   - Use different secrets for different environments
   - Rotate secrets periodically

4. **Token Payload**
   - Keep the payload small (JWT is sent with every request)
   - Don't store sensitive data in the token
   - Use standard claims when possible (e.g., `sub`, `iss`, `exp`)

5. **Security Headers**
   - Always use HTTPS
   - Set appropriate CORS headers
   - Use security headers like `Strict-Transport-Security`

## Complete Example: JWT Login Flow

```php
// Login endpoint
$router->post('/login', function($request) {
    $email = $request->input('email');
    $password = $request->input('password');
    
    // Validate credentials (pseudo-code)
    $user = User::where('email', $email)->first();
    if (!$user || !password_verify($password, $user->password)) {
        http_response_code(401);
        return ['error' => 'Invalid credentials'];
    }
    
    // Generate tokens
    $accessToken = Jwt::generate([
        'user_id' => $user->id,
        'email' => $user->email,
        'roles' => $user->roles
    ], 3600); // 1 hour
    
    $refreshToken = Jwt::generate([
        'user_id' => $user->id,
        'token_type' => 'refresh'
    ], 2592000); // 30 days
    
    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_in' => 3600,
        'token_type' => 'Bearer'
    ];
});
```

This example provides a solid foundation for implementing JWT authentication in your application. Remember to adapt it to your specific security requirements and application architecture.
