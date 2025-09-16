# Directory Overview

The `src/` folder contains all the core classes of **Base**.

```
src/
  Auth.php             // Multi-provider OAuth2 authentication
  AuthMiddleware.php   // Middleware for token validation
  Controller.php       // Base controller class
  Env.php              // Environment variable loader and accessor
  Helper.php           // Static helper utilities
  Logger.php           // PSR-3 compliant logger with Rollbar integration
  Render.php           // Response rendering (JSON, CSV, etc.)
  Request.php          // HTTP request handling and validation
  Router.php           // Router with middleware and handler resolution
```

### Key points

- **Auth** – handles multi-provider OAuth2 login flows (e.g., Google).
- **AuthMiddleware** – validates bearer tokens in requests.
- **Controller** – base class for application controllers.
- **Env** – loads and manages environment variables from `.env`.
- **Helper** – provides static utility methods (e.g., string case conversion).
- **Logger** – PSR-3 compliant logger with built-in Rollbar integration for error tracking.
- **Render** – provides helper methods for rendering JSON and CSV responses.
- **Request** – provides a unified interface for handling HTTP requests, including input retrieval, validation, and sanitization.
- **Router** – handles routing, controller/method resolution, and middleware execution.

This layout ensures Base is modular and extensible.
