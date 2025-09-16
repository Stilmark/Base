# OAuth Login Flow

Use `Auth` to implement Google OAuth2 login.

## Routes
```php
$r->addRoute('GET', '/auth/google', 'BaseApp\\Controller\\AuthController@callout');
$r->addRoute('GET', '/auth/google/callback', 'BaseApp\\Controller\\AuthController@callback');
```

## Controller
```php
namespace BaseApp\Controller;

use Stilmark\Base\Controller;
use Stilmark\Base\Auth;
use Stilmark\Base\Request;

class AuthController extends Controller
{
    protected Auth $auth;

    public function initialize()
    {
        $this->auth = new Auth();
    }

    public function callout()
    {
        $this->auth->callout(); // Redirects to Google
    }

    public function callback()
    {
        $user = $this->auth->callback($this->request);
        // Persist to session, then redirect
        $_SESSION['user'] = $user;
        return $this->json(['login' => 'ok', 'user' => $user]);
    }
}
```

## Google Credentials Setup

Before using OAuth2, you need to obtain Google credentials:

1. Go to the [Google Cloud Console](https://developers.google.com/identity/openid-connect/openid-connect#registeringyourapp)
2. Create a new project or select an existing one
3. Enable the Google+ API
4. Create OAuth 2.0 credentials (Client ID and Client Secret)
5. Add your redirect URI to the authorized redirect URIs

## .env requirements
```
GOOGLE_CLIENT_ID=...          # From Google Cloud Console
GOOGLE_CLIENT_SECRET=...      # From Google Cloud Console
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

## Testing
1. Visit `/auth/google` → consent → callback.
2. Inspect session and response JSON.
