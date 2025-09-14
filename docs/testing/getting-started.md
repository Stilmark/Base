# Testing with Stilmark Base

Guide for testing applications built with Stilmark Base.

## Testing Setup

### PHPUnit Installation

Add PHPUnit to your project:

```bash
composer require --dev phpunit/phpunit
```

### Directory Structure

Organize your tests following PSR-4 conventions:

```
tests/
├── Unit/
│   ├── EnvTest.php
│   ├── RequestTest.php
│   └── ControllerTest.php
├── Integration/
│   ├── AuthTest.php
│   └── RouterTest.php
└── bootstrap.php
```

### Test Bootstrap

Create `tests/bootstrap.php`:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Stilmark\Base\Env;

// Load test environment
Env::load(__DIR__ . '/../.env.testing');

// Set test-specific configurations
Env::set('APP_ENV', 'testing');
Env::set('DB_DATABASE', 'baseapp_test');
```

## Unit Testing Examples

### Testing Environment Management

```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Env;

class EnvTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset environment for each test
        Env::clear();
    }

    public function testCanSetAndGetEnvironmentVariable()
    {
        Env::set('TEST_VAR', 'test_value');
        
        $this->assertEquals('test_value', Env::get('TEST_VAR'));
    }

    public function testReturnsDefaultWhenVariableNotSet()
    {
        $result = Env::get('NON_EXISTENT', 'default');
        
        $this->assertEquals('default', $result);
    }

    public function testCanLoadFromFile()
    {
        $envContent = "TEST_FROM_FILE=file_value\n";
        $tempFile = tempnam(sys_get_temp_dir(), 'env_test');
        file_put_contents($tempFile, $envContent);

        Env::load($tempFile);
        
        $this->assertEquals('file_value', Env::get('TEST_FROM_FILE'));
        
        unlink($tempFile);
    }
}
```

### Testing Request Handling

```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Request;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset superglobals
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testCanGetQueryParameters()
    {
        $_GET['user_id'] = '123';
        
        $request = new Request();
        
        $this->assertEquals('123', $request->get('user_id'));
    }

    public function testCanGetPostData()
    {
        $_POST['username'] = 'testuser';
        
        $request = new Request();
        
        $this->assertEquals('testuser', $request->post('username'));
    }

    public function testCanParseJsonInput()
    {
        $jsonData = json_encode(['key' => 'value']);
        
        // Mock php://input
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getJsonInput'])
            ->getMock();
        
        $request->method('getJsonInput')->willReturn($jsonData);
        
        $this->assertEquals(['key' => 'value'], $request->json());
    }
}
```

### Testing Controllers

```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Controller;

class TestController extends Controller
{
    public function testAction()
    {
        return $this->json(['status' => 'success']);
    }
}

class ControllerTest extends TestCase
{
    public function testJsonResponse()
    {
        $controller = new TestController();
        
        ob_start();
        $controller->testAction();
        $output = ob_get_clean();
        
        $this->assertJson($output);
        $this->assertStringContainsString('success', $output);
    }
}
```

## Integration Testing

### Testing Authentication Flow

```php
<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Auth;
use Stilmark\Base\Env;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up test OAuth credentials
        Env::set('GOOGLE_CLIENT_ID', 'test_client_id');
        Env::set('GOOGLE_CLIENT_SECRET', 'test_secret');
        Env::set('GOOGLE_REDIRECT_URI', 'http://localhost/callback');
    }

    public function testAuthInitialization()
    {
        $auth = new Auth();
        
        $this->assertInstanceOf(Auth::class, $auth);
    }

    public function testCalloutGeneratesRedirectUrl()
    {
        $auth = new Auth();
        
        ob_start();
        $auth->callout();
        $output = ob_get_clean();
        
        // Should redirect to Google OAuth
        $this->assertStringContainsString('accounts.google.com', $output);
    }
}
```

### Testing Router Integration

```php
<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Router;

class RouterTest extends TestCase
{
    public function testRouteDispatch()
    {
        $router = new Router();
        
        $router->addRoute('GET', '/test', function() {
            echo 'Test route works';
        });
        
        // Mock request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        
        ob_start();
        $router->dispatch();
        $output = ob_get_clean();
        
        $this->assertEquals('Test route works', $output);
    }
}
```

## Testing Best Practices

### Environment Isolation

Create a separate `.env.testing` file:

```bash
# .env.testing
APP_ENV=testing
DB_DATABASE=baseapp_test
CACHE_DRIVER=array
SESSION_DRIVER=array
```

### Mocking External Services

```php
public function testOAuthWithMockedService()
{
    $mockProvider = $this->createMock(GoogleProvider::class);
    $mockProvider->method('getAuthorizationUrl')
               ->willReturn('http://mock-oauth-url');
    
    $auth = new Auth($mockProvider);
    // Test with mocked provider
}
```

### Database Testing

```php
protected function setUp(): void
{
    // Start transaction
    DB::beginTransaction();
}

protected function tearDown(): void
{
    // Rollback transaction
    DB::rollback();
}
```

## Running Tests

### Basic Test Execution

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### PHPUnit Configuration

Create `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory>src/</directory>
        </include>
    </coverage>
</phpunit>
```

## Continuous Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php: [8.2, 8.3]
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php }}
        extensions: json, curl
    
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Run tests
      run: ./vendor/bin/phpunit
```

## Performance Testing

### Benchmarking Routes

```php
public function testRoutePerformance()
{
    $router = new Router();
    $router->addRoute('GET', '/api/users/{id}', 'UserController@show');
    
    $start = microtime(true);
    
    for ($i = 0; $i < 1000; $i++) {
        $_SERVER['REQUEST_URI'] = '/api/users/123';
        $router->dispatch();
    }
    
    $duration = microtime(true) - $start;
    
    $this->assertLessThan(1.0, $duration, 'Route resolution too slow');
}
```

This testing guide provides comprehensive examples for testing Stilmark Base applications with proper isolation, mocking, and CI integration.
