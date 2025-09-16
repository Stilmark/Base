# Defining Routes & Controllers

This example shows how to wire routes to controllers using Base's `Router` and `Controller`.

## Directory Layout
```
public/
  index.php
app/
  controllers/
    HelloController.php
```

`/public/index.php`:
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Stilmark\Base\Env;
use Stilmark\Base\Router;

// Start session if you use Auth/session
session_start();

// Load environment variables
Env::load(__DIR__ . '/../.env');

// Dispatch all routes (Router internally uses FastRoute)
$router = new Router();
$router->dispatch();
```

## Defining Routes (inside Router's route map)
The `Router` internally configures `FastRoute\simpleDispatcher` with your application's routes.
Adjust the route callback in `Router` to include your routes, e.g.:

```php
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/hello', 'BaseApp\\Controller\\HelloController@index');
    $r->addRoute('GET', '/hello/{name}', 'BaseApp\\Controller\\HelloController@greet');
});
```

> Handler strings are resolved as `"Namespace\\Class@method"`.> Route parameters (e.g. `{name}`) are bound to controller method arguments.

`/app/controllers/HelloController.php`:
```php
<?php
namespace BaseApp\Controller;

use Stilmark\Base\Controller;

class HelloController extends Controller
{
    public function index()
    {
        return $this->json(['message' => 'Hello World']);
    }

    public function greet(string $name)
    {
        return $this->json(['message' => "Hello {$name}"]);
    }
}
```

## Test with curl
```bash
curl http://localhost:8000/hello
curl http://localhost:8000/hello/Alice
```
