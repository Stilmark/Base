# Bootstrap & Usage Overview

Base provides a lightweight framework for PHP applications.

A typical bootstrap file might look like this:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Stilmark\Base\Env;
use Stilmark\Base\Router;

// Load environment variables
Env::load(__DIR__ . '/../.env');

// Dispatch routes
$router = new Router();
$router->dispatch();
```

This sets up environment variables, initializes the router, and dispatches requests.
