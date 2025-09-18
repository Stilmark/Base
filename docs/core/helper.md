# Helper

Utility functions for common web development tasks including string/array transformations and secure cookie handling.

## Overview

The `Helper` class provides static utility methods for converting between different naming conventions and transforming data structures. It's particularly useful for API integrations and data processing where you need to convert between camelCase and snake_case formats.

## Class Reference

### Static Methods

#### `camelToSnake(string $input): string`

Converts camelCase strings to snake_case.

**Parameters:**
- `$input` (string) - The camelCase string to convert

**Returns:** The converted snake_case string

**Example:**
```php
use Stilmark\Base\Helper;

$result = Helper::camelToSnake('firstName');
// Returns: 'first_name'

$result = Helper::camelToSnake('userAccountId');
// Returns: 'user_account_id'

$result = Helper::camelToSnake('XMLHttpRequest');
// Returns: 'x_m_l_http_request'
```

#### `snakeToCamel(string $input): string`

Converts snake_case strings to CamelCase (PascalCase).

**Parameters:**
- `$input` (string) - The snake_case string to convert

**Returns:** The converted CamelCase string

**Example:**
```php
use Stilmark\Base\Helper;

$result = Helper::snakeToCamel('first_name');
// Returns: 'FirstName'

$result = Helper::snakeToCamel('user_account_id');
// Returns: 'UserAccountId'

$result = Helper::snakeToCamel('api_response_data');
// Returns: 'ApiResponseData'
```

#### `arrayKeysCamelToSnake(array $array): array`

Recursively converts all array keys from camelCase to snake_case.

**Parameters:**
- `$array` (array) - The array with camelCase keys to convert

**Returns:** Array with snake_case keys

**Example:**
```php
use Stilmark\Base\Helper;

$data = [
    'firstName' => 'John',
    'lastName' => 'Doe',
    'userProfile' => [
        'accountId' => 123,
        'emailAddress' => 'john@example.com',
        'profileSettings' => [
            'darkMode' => true,
            'notificationPrefs' => ['email', 'sms']
        ]
    ]
];

$result = Helper::arrayKeysCamelToSnake($data);
// Returns:
// [
//     'first_name' => 'John',
//     'last_name' => 'Doe',
//     'user_profile' => [
//         'account_id' => 123,
//         'email_address' => 'john@example.com',
//         'profile_settings' => [
//             'dark_mode' => true,
//             'notification_prefs' => ['email', 'sms']
//         ]
//     ]
// ]
```

## Cookie Handling

Secure methods for managing HTTP cookies with security best practices.

### `setCookie(string $name, string $value, array $options = []): bool`

Set a secure HTTP cookie with configurable options.

**Parameters:**
- `$name` (string) - Cookie name
- `$value` (string) - Cookie value
- `$options` (array) - Optional settings:
  - `expires` (int) - Expiration time in seconds (default: 0 for session cookie)
  - `path` (string) - Cookie path (default: '/')
  - `domain` (string) - Cookie domain (default: current domain)
  - `secure` (bool) - Only send over HTTPS (default: true in production)
  - `httpOnly` (bool) - Make cookie inaccessible to JavaScript (default: true)
  - `sameSite` (string) - CSRF protection: 'Lax', 'Strict', or 'None' (default: 'Lax')

**Example:**
```php
use Stilmark\Base\Helper;

// Basic secure cookie
Helper::setCookie('preferences', 'dark_theme', [
    'expires' => 86400 * 30,  // 30 days
    'sameSite' => 'Lax'
]);
```

### `setJwtCookie(string $jwt, array $options = []): bool`

Convenience method for setting JWT cookies with secure defaults.

**Parameters:**
- `$jwt` (string) - The JWT token
- `$options` (array) - Same as `setCookie()` plus:
  - `name` (string) - Cookie name (default: 'jwt')

**Example:**
```php
use Stilmark\Base\Helper;

// Set JWT cookie with 1 day expiration
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

Delete a cookie by setting its expiration to the past.

**Parameters:**
- `$name` (string) - Cookie name
- `$options` (array) - Must match options used when setting the cookie

**Example:**
```php
Helper::deleteCookie('jwt', [
    'path' => '/',
    'domain' => 'example.com',
    'secure' => true,
    'httpOnly' => true
]);
```

## Usage Patterns

### Cookie-based Authentication

```php
// After successful login
$token = Jwt::generate(['user_id' => 123]);
Helper::setJwtCookie($token, [
    'expires' => 86400,  // 1 day
    'sameSite' => 'Strict'
]);

// In subsequent requests
if ($jwt = Helper::getCookie('jwt')) {
    try {
        $user = Jwt::validate($jwt);
        // User is authenticated
    } catch (Exception $e) {
        // Handle invalid token
        Helper::deleteCookie('jwt');
    }
}
```

### API Response Transformation

Convert API responses from camelCase to snake_case for database storage:

```php
use Stilmark\Base\Helper;

class UserController extends Controller
{
    public function store()
    {
        $userData = $this->request->json();
        
        // Convert camelCase keys to snake_case for database
        $dbData = Helper::arrayKeysCamelToSnake($userData);
        
        // Save to database with snake_case column names
        $user = User::create($dbData);
        
        return $this->json(['success' => true, 'user' => $user]);
    }
}
```

### Database to API Response

Convert database results to camelCase for API responses:

```php
class UserService
{
    public function getUserProfile(int $userId): array
    {
        // Get user data from database (snake_case columns)
        $userData = DB::table('users')
            ->select('first_name', 'last_name', 'email_address', 'created_at')
            ->where('user_id', $userId)
            ->first();
        
        // Convert to camelCase for API response
        return $this->snakeToCamelKeys($userData);
    }
    
    private function snakeToCamelKeys(array $data): array
    {
        $converted = [];
        foreach ($data as $key => $value) {
            // Convert to camelCase (lowercase first letter)
            $camelKey = lcfirst(Helper::snakeToCamel($key));
            $converted[$camelKey] = $value;
        }
        return $converted;
    }
}
```

### Form Data Processing

Process form submissions with mixed naming conventions:

```php
class ContactController extends Controller
{
    public function submitForm()
    {
        $formData = $this->request->post();
        
        // Normalize all keys to snake_case
        $normalizedData = Helper::arrayKeysCamelToSnake($formData);
        
        // Validate and process
        $validator = new FormValidator($normalizedData);
        
        if ($validator->isValid()) {
            $this->saveContactForm($normalizedData);
            return $this->json(['status' => 'success']);
        }
        
        return $this->json(['status' => 'error', 'errors' => $validator->getErrors()], 400);
    }
}
```

### Configuration Mapping

Map configuration keys between different systems:

```php
class ConfigMapper
{
    public static function mapToExternalApi(array $config): array
    {
        // Convert internal snake_case config to external camelCase API
        $mapped = [];
        
        foreach ($config as $key => $value) {
            $camelKey = lcfirst(Helper::snakeToCamel($key));
            $mapped[$camelKey] = $value;
        }
        
        return $mapped;
    }
    
    public static function mapFromExternalApi(array $apiData): array
    {
        // Convert external camelCase to internal snake_case
        return Helper::arrayKeysCamelToSnake($apiData);
    }
}
```

## Advanced Usage

### Custom Conversion Pipeline

Create a data transformation pipeline:

```php
class DataTransformer
{
    private array $pipeline = [];
    
    public function addStep(callable $step): self
    {
        $this->pipeline[] = $step;
        return $this;
    }
    
    public function transform(array $data): array
    {
        foreach ($this->pipeline as $step) {
            $data = $step($data);
        }
        return $data;
    }
    
    public static function create(): self
    {
        return new self();
    }
}

// Usage
$transformer = DataTransformer::create()
    ->addStep([Helper::class, 'arrayKeysCamelToSnake'])
    ->addStep(function($data) {
        // Additional custom transformation
        return array_filter($data, fn($value) => $value !== null);
    });

$result = $transformer->transform($inputData);
```

### Batch Processing

Process multiple arrays with consistent key conversion:

```php
class BatchProcessor
{
    public static function convertArraysBatch(array $arrays, string $conversion = 'camelToSnake'): array
    {
        return array_map(function($array) use ($conversion) {
            switch ($conversion) {
                case 'camelToSnake':
                    return Helper::arrayKeysCamelToSnake($array);
                default:
                    return $array;
            }
        }, $arrays);
    }
}

// Usage
$userRecords = [
    ['firstName' => 'John', 'lastName' => 'Doe'],
    ['firstName' => 'Jane', 'lastName' => 'Smith'],
    ['firstName' => 'Bob', 'lastName' => 'Johnson']
];

$converted = BatchProcessor::convertArraysBatch($userRecords);
```

## Performance Considerations

### Caching Conversions

For frequently converted strings, consider caching:

```php
class CachedHelper
{
    private static array $camelToSnakeCache = [];
    private static array $snakeToCamelCache = [];
    
    public static function camelToSnake(string $input): string
    {
        if (!isset(self::$camelToSnakeCache[$input])) {
            self::$camelToSnakeCache[$input] = Helper::camelToSnake($input);
        }
        
        return self::$camelToSnakeCache[$input];
    }
    
    public static function snakeToCamel(string $input): string
    {
        if (!isset(self::$snakeToCamelCache[$input])) {
            self::$snakeToCamelCache[$input] = Helper::snakeToCamel($input);
        }
        
        return self::$snakeToCamelCache[$input];
    }
}
```

### Memory Optimization

For large arrays, consider processing in chunks:

```php
class OptimizedHelper
{
    public static function arrayKeysCamelToSnakeChunked(array $array, int $chunkSize = 1000): array
    {
        if (count($array) <= $chunkSize) {
            return Helper::arrayKeysCamelToSnake($array);
        }
        
        $result = [];
        $chunks = array_chunk($array, $chunkSize, true);
        
        foreach ($chunks as $chunk) {
            $converted = Helper::arrayKeysCamelToSnake($chunk);
            $result = array_merge($result, $converted);
        }
        
        return $result;
    }
}
```

## Testing

### Unit Tests

```php
class HelperTest extends TestCase
{
    public function testCamelToSnake()
    {
        $this->assertEquals('first_name', Helper::camelToSnake('firstName'));
        $this->assertEquals('user_account_id', Helper::camelToSnake('userAccountId'));
        $this->assertEquals('simple', Helper::camelToSnake('simple'));
    }
    
    public function testSnakeToCamel()
    {
        $this->assertEquals('FirstName', Helper::snakeToCamel('first_name'));
        $this->assertEquals('UserAccountId', Helper::snakeToCamel('user_account_id'));
        $this->assertEquals('Simple', Helper::snakeToCamel('simple'));
    }
    
    public function testArrayKeysCamelToSnake()
    {
        $input = [
            'firstName' => 'John',
            'userProfile' => [
                'accountId' => 123
            ]
        ];
        
        $expected = [
            'first_name' => 'John',
            'user_profile' => [
                'account_id' => 123
            ]
        ];
        
        $this->assertEquals($expected, Helper::arrayKeysCamelToSnake($input));
    }
}
```

## Security Considerations

### Cookie Security

1. **Always use secure cookies in production**:
   - Set `secure` flag to true (default)
   - Always use `httpOnly` flag (default)
   - Set appropriate `sameSite` attribute (default: 'Lax')
   - Keep expiration times reasonable

2. **JWT in Cookies**:
   - Always use `httpOnly` cookies for JWT storage
   - Set appropriate `sameSite` policy based on your needs
   - Consider using refresh tokens for better security
   - Implement proper token expiration and renewal

## Best Practices

1. **Consistency**: Choose one naming convention and stick with it throughout your application
2. **API Boundaries**: Convert data at API boundaries rather than throughout your application
3. **Performance**: Cache frequently converted strings for better performance
4. **Validation**: Validate data after conversion to ensure integrity
5. **Documentation**: Document your naming conventions and conversion patterns

## Common Use Cases

- **API Integration**: Converting between different API naming conventions
- **Database Mapping**: Mapping between database column names and object properties
- **Form Processing**: Normalizing form field names
- **Configuration Management**: Converting configuration keys between systems
- **Data Import/Export**: Transforming data formats during import/export operations