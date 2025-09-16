# Auth

The `Auth` class provides a simple interface for OAuth2 authentication, acting as a wrapper for the `league/oauth2-client` library. It is designed to be multi-provider, with Google implemented by default.

**Repository:** [thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client)

**Detailed Example:** See [examples/oauth.md](../examples/oauth.md) for a complete implementation.

## Environment Variables

The class relies on environment variables for configuration. You must set these for each provider you intend to use.

```
# The session key where authentication data is stored
AUTH_SESSION_NAME=auth

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
    -   Storing a comprehensive authentication payload in the session (e.g., `$_SESSION['auth']`).
    -   Returning the user's profile and status.
4.  **`logout()`**: This method removes the authentication data from the session, effectively logging the user out.

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
