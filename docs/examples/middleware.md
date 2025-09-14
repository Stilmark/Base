# Route Protection with Middleware

Protect endpoints by inserting middleware into the route definition.

## Route definition with middleware
```php
$r->addRoute(
    'GET',
    '/admin',
    [
        'handler' => 'App\\Http\\Controllers\\AdminController@index',
        'middlewares' => [
            'App\\Http\\Middleware\\AuthGate',
        ],
    ]
);
```

## Middleware
```php
namespace App\Http\Middleware;

use Stilmark\Base\AuthMiddleware;

class AuthGate extends AuthMiddleware
{
    protected function validateToken(?string $token): bool
    {
        // Example: accept a fixed token from env (replace with real validation)
        $expected = \Stilmark\Base\Env::get('API_TOKEN');
        if (!$token && isset($_SESSION['user'])) {
            return true; // session-based access
        }
        return $token && preg_match('/^Bearer\s+(.+)/i', $token) && trim(substr($token, 7)) === $expected;
    }
}
```

## Controller
```php
use Stilmark\Base\Controller;

class AdminController extends Controller
{
    public function index()
    {
        return $this->json(['admin' => true]);
    }
}
```

## Test with curl
```bash
# Without token (should 401)
curl -i http://localhost:8000/admin

# With token
curl -i -H "Authorization: Bearer $API_TOKEN" http://localhost:8000/admin
```
