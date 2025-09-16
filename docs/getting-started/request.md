# Handling Requests

- **Core Class:** [Request](../core/request.md)
- **Example:** [Request Class Usage](../examples/request-usage.md)

---

The `Request` class wraps HTTP request data.

### Accessing GET and POST parameters

```php
use Stilmark\Base\Request;

$request = new Request();

$name = $request->get('name');   // From query string
$data = $request->post('data');  // From POST body
```

### JSON body

```php
$json = $request->json();
```

### Headers and cookies

```php
$token = $request->header('Authorization');
$session = $request->cookie('PHPSESSID');
```

### File uploads

```php
$file = $request->file('avatar');
```
