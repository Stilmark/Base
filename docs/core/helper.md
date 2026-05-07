# Helper

Utility functions for common web development tasks including string conversions, JSON handling, array utilities, and secure cookie handling.

## Overview

The `Helper` class provides static utility methods organized into four categories:

- **String Conversion**: Convert between camelCase, PascalCase, and snake_case
- **JSON Utilities**: Safe JSON encoding/decoding with defaults
- **Array Utilities**: Filter, access, and manipulate arrays
- **Cookie Handling**: Secure cookie management with best practices

---

## String Conversion

Convert between different naming conventions.

### `toCamelCase(string $input): string`

Converts any string to camelCase (lowercase first letter).

**Parameters:**
- `$input` (string) - The string to convert

**Returns:** camelCase string

**Example:**
```php
Helper::toCamelCase('first_name');
// Returns: 'firstName'

Helper::toCamelCase('user_account_id');
// Returns: 'userAccountId'

Helper::toCamelCase('FirstName');
// Returns: 'firstName'
```

### `toSnakeCase(string $input): string`

Converts any string to snake_case.

**Parameters:**
- `$input` (string) - The string to convert

**Returns:** snake_case string

**Example:**
```php
Helper::toSnakeCase('firstName');
// Returns: 'first_name'

Helper::toSnakeCase('userAccountId');
// Returns: 'user_account_id'

Helper::toSnakeCase('XMLHttpRequest');
// Returns: 'x_m_l_http_request'
```

### `camelToSnake(string $input): string`

Converts camelCase strings to snake_case.

**Parameters:**
- `$input` (string) - The camelCase string to convert

**Returns:** snake_case string

**Example:**
```php
Helper::camelToSnake('firstName');
// Returns: 'first_name'

Helper::camelToSnake('userAccountId');
// Returns: 'user_account_id'
```

### `snakeToCamel(string $input): string`

Converts snake_case strings to PascalCase (CamelCase with uppercase first letter).

**Parameters:**
- `$input` (string) - The snake_case string to convert

**Returns:** PascalCase string

**Example:**
```php
Helper::snakeToCamel('first_name');
// Returns: 'FirstName'

Helper::snakeToCamel('user_account_id');
// Returns: 'UserAccountId'
```

---

## JSON Utilities

Safe JSON encoding and decoding with sensible defaults.

### `jsonDecode(mixed $json, array $default = []): array`

Safely decode JSON string to array. Returns default value on null, empty, or invalid JSON.

**Parameters:**
- `$json` (mixed) - JSON string or null
- `$default` (array) - Default value if decode fails (default: empty array)

**Returns:** Decoded array or default

**Example:**
```php
$company = ['billing' => '{"card": "1234"}'];

$billing = Helper::jsonDecode($company['billing'] ?? null);
// Returns: ['card' => '1234']

$settings = Helper::jsonDecode($company['settings'] ?? null, ['theme' => 'light']);
// Returns: ['theme' => 'light'] (default because settings is null)
```

### `jsonEncode(mixed $data, bool $pretty = false): string`

Encode data to JSON string. Returns empty string on failure.

**Parameters:**
- `$data` (mixed) - Data to encode
- `$pretty` (bool) - Pretty print output (default: false)

**Returns:** JSON string or empty string on failure

**Example:**
```php
$data = ['name' => 'John', 'email' => 'john@example.com'];

Helper::jsonEncode($data);
// Returns: '{"name":"John","email":"john@example.com"}'

Helper::jsonEncode($data, true);
// Returns formatted JSON with indentation
```

### `isJson(mixed $string): bool`

Check if a string is valid JSON.

**Parameters:**
- `$string` (mixed) - String to check

**Returns:** True if valid JSON

**Example:**
```php
Helper::isJson('{"name": "John"}');
// Returns: true

Helper::isJson('not json');
// Returns: false

Helper::isJson(null);
// Returns: false
```

---

## Array Utilities

Filter, access, and manipulate arrays.

### `arrayOnly(array $source, array $keys): array`

Keep only specified keys from an array.

**Parameters:**
- `$source` (array) - The source array
- `$keys` (array) - Keys to keep

**Returns:** Array with only the specified keys

**Example:**
```php
$data = ['name' => 'John', 'email' => 'john@example.com', 'password' => 'secret'];
$result = Helper::arrayOnly($data, ['name', 'email']);
// Returns: ['name' => 'John', 'email' => 'john@example.com']
```

### `arrayExcept(array $source, array $keys): array`

Remove specified keys from an array.

**Parameters:**
- `$source` (array) - The source array
- `$keys` (array) - Keys to remove

**Returns:** Array without the specified keys

**Example:**
```php
$data = ['name' => 'John', 'email' => 'john@example.com', 'password' => 'secret'];
$result = Helper::arrayExcept($data, ['password']);
// Returns: ['name' => 'John', 'email' => 'john@example.com']
```

### `arrayGet(array $array, string $key, $default = null)`

Get a value from nested array using dot notation.

**Parameters:**
- `$array` (array) - The source array
- `$key` (string) - Dot-notation key (e.g., 'user.profile.name')
- `$default` (mixed) - Default value if key not found

**Returns:** The value or default

**Example:**
```php
$data = [
    'user' => [
        'profile' => [
            'name' => 'John',
            'email' => 'john@example.com'
        ]
    ]
];

$name = Helper::arrayGet($data, 'user.profile.name');
// Returns: 'John'

$phone = Helper::arrayGet($data, 'user.profile.phone', 'N/A');
// Returns: 'N/A'
```

### `arrayHas(array $array, string $key): bool`

Check if array has a value at dot-notation key.

**Parameters:**
- `$array` (array) - The source array
- `$key` (string) - Dot-notation key

**Returns:** True if key exists

**Example:**
```php
$data = [
    'user' => [
        'profile' => [
            'name' => 'John'
        ]
    ]
];

if (Helper::arrayHas($data, 'user.profile.name')) {
    // Key exists
}
```

### `arrayKeysCamelToSnake(array $array): array`

Recursively converts all array keys from camelCase to snake_case.

**Parameters:**
- `$array` (array) - The array with camelCase keys to convert

**Returns:** Array with snake_case keys

**Example:**
```php
$data = [
    'firstName' => 'John',
    'userProfile' => [
        'accountId' => 123,
        'emailAddress' => 'john@example.com'
    ]
];

$result = Helper::arrayKeysCamelToSnake($data);
// Returns:
// [
//     'first_name' => 'John',
//     'user_profile' => [
//         'account_id' => 123,
//         'email_address' => 'john@example.com'
//     ]
// ]
```

---

## Cookie Handling

Secure methods for managing HTTP cookies.

### `setCookie(string $name, string $value, array $options = []): bool`

Set a secure HTTP cookie.

**Parameters:**
- `$name` (string) - Cookie name
- `$value` (string) - Cookie value
- `$options` (array) - Optional settings:
  - `expires` (int) - Expiration time in seconds (default: 0 for session)
  - `path` (string) - Cookie path (default: '/')
  - `domain` (string) - Cookie domain (default: current domain)
  - `secure` (bool) - HTTPS only (default: true in production)
  - `httpOnly` (bool) - No JavaScript access (default: true)
  - `sameSite` (string) - 'Lax', 'Strict', or 'None' (default: 'Lax')

**Example:**
```php
Helper::setCookie('preferences', 'dark_theme', [
    'expires' => 86400 * 30,  // 30 days
    'sameSite' => 'Lax'
]);
```

### `setJwtCookie(string $jwt, array $options = []): bool`

Set a JWT cookie with secure defaults.

**Parameters:**
- `$jwt` (string) - The JWT token
- `$options` (array) - Same as `setCookie()` plus:
  - `name` (string) - Cookie name (default: 'jwt')

**Example:**
```php
Helper::setJwtCookie($token, [
    'expires' => 86400,
    'sameSite' => 'Strict'
]);
```

### `getCookie(string $name, $default = null)`

Get a cookie value by name.

**Parameters:**
- `$name` (string) - Cookie name
- `$default` (mixed) - Default value if cookie doesn't exist

**Returns:** Cookie value or default

**Example:**
```php
$theme = Helper::getCookie('theme', 'light');
```

### `deleteCookie(string $name, array $options = []): bool`

Delete a cookie.

**Parameters:**
- `$name` (string) - Cookie name
- `$options` (array) - Must match options used when setting the cookie

**Example:**
```php
Helper::deleteCookie('jwt', [
    'path' => '/',
    'domain' => 'example.com'
]);
```

---

## Usage Examples

### Cookie-based Authentication

```php
// After login
$token = Jwt::generate(['user_id' => 123]);
Helper::setJwtCookie($token, ['expires' => 86400, 'sameSite' => 'Strict']);

// In subsequent requests
if ($jwt = Helper::getCookie('jwt')) {
    try {
        $user = Jwt::validate($jwt);
        // User authenticated
    } catch (Exception $e) {
        Helper::deleteCookie('jwt');
    }
}
```

### API Response Transformation

Convert camelCase API responses to snake_case for database:

```php
class UserController extends Controller
{
    public function store()
    {
        $userData = $this->request->json();
        $dbData = Helper::arrayKeysCamelToSnake($userData);
        $user = User::create($dbData);
        return $this->json(['success' => true, 'user' => $user]);
    }
}
```

### Database to API Response

Convert snake_case database results to camelCase:

```php
class UserService
{
    public function getUserProfile(int $userId): array
    {
        $userData = DB::table('users')
            ->select('first_name', 'last_name', 'email_address')
            ->where('user_id', $userId)
            ->first();
        
        return $this->snakeToCamelKeys($userData);
    }
    
    private function snakeToCamelKeys(array $data): array
    {
        $converted = [];
        foreach ($data as $key => $value) {
            $converted[Helper::toCamelCase($key)] = $value;
        }
        return $converted;
    }
}
```

### Filtering Sensitive Data

```php
$user = [
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => 'secret',
    'api_key' => 'abc123'
];

// Return to client (exclude sensitive fields)
$publicUser = Helper::arrayExcept($user, ['password', 'api_key']);
```