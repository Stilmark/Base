# Stilmark Base

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue.svg)](https://php.net)
[![Composer](https://img.shields.io/badge/Composer-PSR--4-orange.svg)](https://getcomposer.org)
[![CodeFactor](https://www.codefactor.io/repository/github/stilmark/base/badge)](https://www.codefactor.io/repository/github/stilmark/base)

A lightweight PHP utility library providing essential functionality for modern web applications. Base serves as a foundation for building PHP applications with clean, reusable components.

## Features

- **Environment Management** - Flexible `.env` file handling with runtime overrides
- **Request Handling** - Parse GET, POST, JSON, headers, cookies, and file uploads
- **Routing System** - Fast routing with middleware support using FastRoute
- **Base Controller** - Foundation controller with JSON and redirect helpers
- **Response Rendering** - Built-in JSON and CSV response formatting
- **Authentication** - Google OAuth2 integration with session management
- **Middleware** - Extensible middleware system for route protection
- **Logging** - Structured logging capabilities
- **Lightweight** - Minimal dependencies, maximum performance

## Requirements

- PHP 8.2 or higher
- Composer
- Extensions: `json`, `curl` (for OAuth)

## Installation

Install via Composer:

```bash
composer require stilmark/base
```

## Quick Start

### 1. Basic Setup

Create a bootstrap file (`index.php`):

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Stilmark\Base\Env;
use Stilmark\Base\Router;

// Load environment variables
Env::load(__DIR__ . '/.env');

// Initialize and dispatch routes
$router = new Router();
$router->addRoute('GET', '/', 'BaseApp\\Controller\\HomeController@index');
$router->dispatch();
```

### 2. Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
cp vendor/stilmark/base/.env.example .env
```

### 3. Create Your First Controller

```php
<?php
namespace BaseApp\Controller;

use Stilmark\Base\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to Stilmark Base!',
            'timestamp' => date('c')
        ]);
    }
}
```

## Core Components

| Component | Description |
|-----------|-------------|
| **Env** | Environment variable management with `.env` support |
| **Request** | HTTP request parsing and data extraction |
| **Router** | Fast routing with middleware and parameter support |
| **Controller** | Base controller with common response helpers |
| **Render** | Response formatting for JSON, CSV, and more |
| **Auth** | Google OAuth2 authentication flow |
| **AuthMiddleware** | Route protection and session management |
| **Logger** | Structured logging with multiple output formats |

## Usage Examples

### Environment Variables
```php
use Stilmark\Base\Env;

Env::load('.env');
$apiUrl = Env::get('API_URL', 'https://api.default.com');
Env::set('RUNTIME_FLAG', true);
```

### Request Handling
```php
use Stilmark\Base\Request;

$request = new Request();
$userId = $request->get('user_id');
$jsonData = $request->json();
$uploadedFile = $request->file('avatar');
```

### Authentication
```php
use Stilmark\Base\Auth;

$auth = new Auth();
$auth->callout(); // Redirect to Google OAuth

// In callback route:
$user = $auth->callback($request);
$_SESSION['user'] = $user;
```

## Documentation

 **[Complete Documentation](https://stilmark-dev.gitbook.io/base/)**

- [Installation Guide](https://stilmark-dev.gitbook.io/base/intro/installation)
- [Getting Started](https://stilmark-dev.gitbook.io/base/getting-started/overview)
- [Core Classes Reference](https://stilmark-dev.gitbook.io/base/core/)
- [Examples & Tutorials](https://stilmark-dev.gitbook.io/base/examples/)
- [Configuration](https://stilmark-dev.gitbook.io/base/config/)

## Related Projects

- **[BaseApp](https://github.com/Stilmark/BaseApp)** - Full application framework built on Base

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](docs/appendix/license.md) file for details.

## Author

**Christian Lund**  
- Email: clund@stilmark.com
- Website: [stilmark.com](http://stilmark.com)

---

*Built with by [Stilmark](http://stilmark.com)*