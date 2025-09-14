# Controller

Base controller for your application controllers.

## Lifecycle
- Constructs a `Request` instance
- Calls `initialize()` hook if present

## Public API (helpers)
```php
void json(mixed $data, int $statusCode = 200)
void redirect(string $url, int $statusCode = 302)
```

## Example
```php
use Stilmark\Base\Controller;

class UsersController extends Controller {
    public function initialize() {
        // preload data, guards, etc.
    }

    public function show() {
        return $this->json(['ok' => true]);
    }
}
```
