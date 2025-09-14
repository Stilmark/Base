# OAuth Login Flow

Use `Auth` to implement Google OAuth2 login.

## Routes
```php
$r->addRoute('GET', '/auth/google', 'App\\Http\\Controllers\\AuthController@callout');
$r->addRoute('GET', '/auth/google/callback', 'App\\Http\\Controllers\\AuthController@callback');
```

## Controller
```php
namespace App\Http\Controllers;

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

## .env requirements
```
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

## Testing
1. Visit `/auth/google` → consent → callback.
2. Inspect session and response JSON.
