# AuthMiddleware

Middleware to guard routes by validating bearer tokens (or session state).

## Contract
```php
bool handle()        // return false to block the request
protected bool validateToken(?string $token)  // implement your logic
```

## Example
```php
$mw = new AuthMiddleware();
if (!$mw->handle()) {
    http_response_code(401);
    exit('Unauthorized');
}
```
