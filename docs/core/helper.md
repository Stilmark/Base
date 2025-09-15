# Helper

Utility functions for common string and array transformations.

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

## Usage Patterns

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

## Best Practices

1. **Consistent Naming**: Use Helper methods to maintain consistent naming conventions across your application
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