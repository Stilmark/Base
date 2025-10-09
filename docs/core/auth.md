# Auth

The `Auth` class provides a simple interface for OAuth2 authentication, acting as a wrapper for the `league/oauth2-client` library. It is designed to be multi-provider, with Google implemented by default.

**Repository:** [thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client)

**Detailed Example:** See [examples/oauth.md](../examples/oauth.md) for a complete implementation.

## Environment Variables

The class relies on environment variables for configuration. You must set these for each provider you intend to use.

```
# The session key where authentication data is stored
SESSION_AUTH_NAME=auth

# Google Provider
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=/auth/google/callback

# Microsoft Provider (Example - Not yet implemented)
# MICROSOFT_CLIENT_ID=...
# MICROSOFT_CLIENT_SECRET=...
# MICROSOFT_REDIRECT_URI=/auth/microsoft/callback
```

## Authentication Flow

1.  **`__construct(string $providerType)`**: An `Auth` instance is created, specifying the provider (e.g., `'google'`).
2.  **`callout()`**: This method generates the provider's authorization URL, saves the state and provider type to the session, and redirects the user to the provider's login page.
3.  **`callback(Request $request)`**: After the user authenticates, the provider redirects back to your application. This method handles the callback by:
    -   Validating the `state` parameter to prevent CSRF attacks.
    -   Exchanging the authorization `code` for an access token.
    -   Fetching the user's profile from the provider.
    -   **Regenerating the session ID** to prevent session fixation attacks.
    -   Storing a comprehensive authentication payload in the session (e.g., `$_SESSION['auth']`).
    -   **Setting login and activity timestamps** for timeout tracking.
    -   Returning the user's profile and status.
4.  **`logout()`**: This method removes the authentication data from the session and completely destroys the session using `Session::destroy()`.

## Public API

```php
// Initializes the provider (e.g., 'google')
__construct(string $providerType = 'google')

// Redirects the user to the provider's authorization page
callout(): void

// Handles the OAuth2 callback and returns user data
callback(Request $request): array

// Clears authentication data from the session
logout(): void
```

## Example

```php
// In your login route
$auth = new Auth('google');
$auth->callout(); // Redirects to Google

// In your callback route: /auth/google/callback
$auth = new Auth('google');
$result = $auth->callback($request);

if ($result['status'] === 'success') {
    // Redirect to a protected area
    header('Location: /dashboard');
    exit;
}

// In your logout route
$auth = new Auth();
$auth->logout();
header('Location: /');
exit;
```

## Security Features

The `Auth` class implements several security best practices:

1. **Session Regeneration**: After successful authentication, the session ID is regenerated using `Session::regenerate()` to prevent session fixation attacks.
2. **Timeout Tracking**: Login time and last activity timestamps are automatically set for use with `AuthMiddleware` timeout validation.
3. **Secure Logout**: The `logout()` method uses `Session::destroy()` to completely clear session data, delete the session cookie, and destroy the session file.
4. **State Validation**: OAuth2 state parameter is validated to prevent CSRF attacks during the authentication flow.

## Session Data Structure

After successful authentication, the following data is stored in `$_SESSION['auth']` (or your configured session key):

```php
[
    'access_token' => '...',      // OAuth2 access token
    'token_expires' => 1234567890, // Token expiration timestamp
    'refresh_token' => '...',     // OAuth2 refresh token (if available)
    'user' => [...],              // User profile data from provider
    'provider' => 'google',       // Provider name
    'auth_time' => 1234567890     // Authentication timestamp
]
```

Additionally, the following timestamps are set for timeout management:
- `$_SESSION['login_time']` - Absolute login timestamp
- `$_SESSION['last_activity']` - Last activity timestamp (updated by `AuthMiddleware`)

## See Also

- [AuthMiddleware](auth-middleware.md) - For protecting routes with session validation
- [Session](session.md) - For session management utilities
- [OAuth Example](../examples/oauth.md) - Complete OAuth implementation example
