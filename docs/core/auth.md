# Auth

Google OAuth2 authentication using `league/oauth2-client` and the Google provider.

**Repository:** [thephpleague/oauth2-google](https://github.com/thephpleague/oauth2-google)

**Detailed Example:** See [examples/oauth.md](../examples/oauth.md) for complete implementation.

## Environment
```
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://baseapp.com/oauth/callback
```

## Flow
- `callout()` builds authorization URL and redirects user
- `callback($request)` exchanges code for token and returns user/token payload
- Typically stores user info in session

## API (typical)
```php
__construct()
void callout()                         // redirect
array|object callback(Request $request) // returns profile/token
```

## Example
```php
$auth = new Auth();
$auth->callout(); // redirect to Google
// ... in callback route:
$result = $auth->callback($request);
```
