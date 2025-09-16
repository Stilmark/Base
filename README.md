# Stilmark Base

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue.svg)](https://php.net)
[![Composer](https://img.shields.io/badge/Composer-PSR--4-orange.svg)](https://getcomposer.org)
[![CodeFactor](https://www.codefactor.io/repository/github/stilmark/base/badge)](https://www.codefactor.io/repository/github/stilmark/base)

A lightweight PHP utility library providing essential functionality for modern web applications. Base serves as a foundation for building PHP applications with clean, reusable components.

## Features

- **Environment Management**: Load and access `.env` variables.
- **Request Handling**: A unified interface for HTTP requests, including input retrieval, validation, and sanitization.
- **Routing**: A simple and fast router with middleware support.
- **Controllers**: A base controller to extend for application logic.
- **Response Rendering**: Helpers for sending JSON and CSV responses.
- **Authentication**: Multi-provider OAuth2 support (e.g., Google) and middleware for protecting routes.
- **Logging**: PSR-3 compliant logging with built-in Rollbar integration.
- **Helper Utilities**: Static methods for common tasks like string manipulation.

## Requirements

- **PHP**: version 8.2 or higher
- **Composer** for dependency management

### Dependencies

Base relies on the following libraries (installed via Composer):

- [symfony/dotenv](https://github.com/symfony/dotenv) – for loading environment variables from `.env` files
- [nikic/fast-route](https://github.com/nikic/FastRoute) – for routing
- [league/oauth2-client](https://github.com/thephpleague/oauth2-client) – for OAuth2 authentication
- [league/oauth2-google](https://github.com/thephpleague/oauth2-google) – Google OAuth2 provider
- [rollbar/rollbar](https://github.com/rollbar/rollbar-php) – for error tracking and monitoring

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
|---|---|
| **Env** | Loads and manages environment variables from `.env`. |
| **Request** | Provides a unified interface for handling HTTP requests, including input retrieval, validation, and sanitization. |
| **Router** | Handles routing, controller/method resolution, and middleware execution. |
| **Controller** | Base class for application controllers. |
| **Render** | Provides helper methods for rendering JSON and CSV responses. |
| **Auth** | Handles multi-provider OAuth2 login flows (e.g., Google). |
| **AuthMiddleware** | Validates bearer tokens in requests. |
| **Logger** | PSR-3 compliant logger with built-in Rollbar integration. |
| **Helper** | Provides static utility methods (e.g., string case conversion). |

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

## License

This project is licensed under the MIT License - see the [LICENSE](docs/appendix/license.md) file for details.

## Author

**Christian Lund**  
- Email: clund@stilmark.com
- Website: [stilmark.com](http://stilmark.com)
