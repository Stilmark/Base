# Auth

Google OAuth2 authentication using `league/oauth2-client` and the Google provider.

## Environment
```
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://your-app.com/oauth/callback
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
