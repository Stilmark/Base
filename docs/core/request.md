# Request

The `Request` class provides a unified, object-oriented interface for interacting with the current HTTP request. It simplifies access to `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, and `$_SERVER` superglobals, and provides helpers for handling JSON input, headers, and validation.

## Capabilities

- Access GET, POST, and JSON data.
- Check for the existence of parameters.
- Validate input data against a set of rules.
- Sanitize input to prevent XSS attacks.
- Access headers, cookies, server variables, and uploaded files.
- Helpers for request method, URI, and AJAX detection.

## Public API

### Data Retrieval

```php
// Get all input data (GET, POST, JSON)
all(): array

// Get a value from any source (GET > POST > JSON)
query(string $key, $default = null)

// Get a value from query string ($_GET)
get(string $key, $default = null)

// Get a value from a POST form ($_POST)
post(string $key, $default = null)

// Get the full JSON body or a specific key
json(?string $key = null, $default = null)
```

### Existence Checks

```php
// Check if GET key(s) exist and get their values
hasGet(string|array $keys): array|false

// Check if POST key(s) exist and get their values
hasPost(string|array $keys): array|false

// Check if JSON key(s) exist and get their values
hasJson(string|array $keys): array|false
```

### Validation

```php
// Validate GET data against rules
validateGet(array $rules): array

// Validate POST data against rules
validatePost(array $rules): array

// Validate JSON data against rules
validateJson(array $rules): array

// Validate an uploaded file
validateFile(string $key, array $allowedTypes = [], int $maxSize = 5242880): array|false

// Validate a CSRF token
validateCsrfToken(string $sessionTokenKey = 'csrf_token'): bool
```

### Sanitization

```php
// Get a sanitized value (XSS protection)
safe(string $key, $default = null): string

// Get and validate an email address
email(string $key, ?string $default = null): ?string

// Sanitize a string
sanitize(string $input): string
```

### Request Metadata

```php
// Get a request header
header(string $key, $default = null)

// Get a server variable
server(string $key, $default = null)

// Get the request method (e.g., 'GET', 'POST')
method(): string

// Get the request URI
uri(): string

// Check if it's an AJAX request
isAjax(): bool

// Get a cookie value
cookie(string $key, $default = null)

// Get an uploaded file
file(string $key, $default = null)
```

## Basic Example

```php
use Stilmark\Base\Request;

$request = new Request();

// Get a user ID from the query string: /?id=123
$userId = $request->get('id');

// Get the full JSON payload from a POST request
$payload = $request->json();

// Get a specific header
$token = $request->header('Authorization');

// Check the request method
if ($request->method() === 'POST') {
    // Handle POST request
}
```
