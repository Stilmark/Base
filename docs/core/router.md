# Router

The `Router` integrates [FastRoute](https://github.com/nikic/FastRoute) and resolves handlers and middleware.

## Overview
- Dispatches incoming requests
- Resolves handlers like `"BaseApp\\Controller\\UsersController@index"`
- Binds route params to controller method arguments
- Executes middleware chain before action

## Public API
```php
void dispatch()
```

## Handler Resolution
- `"Namespace\\Class@method"` → instantiate class and invoke method
- Return values:
  - `array` → sent as JSON
  - `string`/`void` → treated as already rendered

## Middleware
Each middleware must implement a `handle(): bool` method. If any returns `false`, the request is halted.

## Example
```php
$router = new Router();
$router->dispatch();
```
