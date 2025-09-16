# Routing & Controllers

**Core Class:** [Router](../core/router.md)
**Example:** [Defining Routes & Controllers](../examples/routes-controllers.md)

Base uses [FastRoute](https://github.com/nikic/FastRoute) for routing.

### Defining routes

```php
use Stilmark\Base\Router;

$router = new Router();
$router->dispatch();
```

Routes are defined in a callback passed to `FastRoute\simpleDispatcher` inside `Router`.

### Controllers

Controllers extend the `Controller` base class.

```php
use Stilmark\Base\Controller;

class HelloController extends Controller {
    public function index() {
        return $this->json(['message' => 'Hello World']);
    }
}
```

### Middleware

Routes can include middleware that run before the controller action.
