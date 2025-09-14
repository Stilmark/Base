# Request

The `Request` class provides a unified interface over PHP superglobals and request metadata.

## Capabilities
- Query (`$_GET`) and form (`$_POST`) access
- JSON body parsing/caching
- Header lookup (case-insensitive)
- Method & URI helpers
- Cookies and file uploads

## Public API (common)
```php
array all()
mixed get(string $key, mixed $default = null)       // Query string
mixed post(string $key, mixed $default = null)      // POST form fields
mixed json(string $key = null, mixed $default = null)
mixed header(string $key, mixed $default = null)
mixed server(string $key, mixed $default = null)
string method()
string uri()
bool isAjax()
mixed cookie(string $key, mixed $default = null)
mixed file(string $key, mixed $default = null)
```

## Examples
```php
use Stilmark\Base\Request;

$r = new Request();
$userId = $r->get('user_id');
$payload = $r->json();
$token = $r->header('Authorization');
```
