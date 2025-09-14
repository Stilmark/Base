# Sessions & Cookies

Base does not enforce a session strategy but provides helpers via the `Request` class.

### Cookies

Access cookies with:

```php
use Stilmark\Base\Request;

$request = new Request();
$sessionId = $request->cookie('PHPSESSID');
```

### Sessions

The `Auth` class uses PHP sessions by default to store user tokens after OAuth login.

```php
$_SESSION['user'] = $user;
```

To enable sessions, ensure `session_start()` is called in your bootstrap file:

```php
session_start();
```

### Best Practices

- Use secure cookies (`Secure`, `HttpOnly`) in production.  
- Regenerate session IDs after login to prevent fixation.  
- Store minimal data in sessions; prefer tokens for authentication.
