# Directory Overview

The `src/` folder contains all the core classes of **Base**.

```
src/
  Auth.php             // Google OAuth authentication
  AuthMiddleware.php   // Middleware for token validation
  Controller.php       // Base controller class
  Env.php              // Environment loader and accessor
  Logger.php           // Logger (placeholder for future use)
  Render.php           // Response rendering (JSON, CSV, etc.)
  Request.php          // HTTP request parser
  Router.php           // Router with middleware and handler resolution
```

### Key points

- **Auth** – handles Google OAuth2 login and callback flows  
- **AuthMiddleware** – validates bearer tokens in requests  
- **Controller** – base class for application controllers  
- **Env** – loads and manages environment variables from `.env`  
- **Logger** – placeholder class for logging (to be expanded)  
- **Render** – provides helper methods for rendering JSON and CSV responses  
- **Request** – central request parser for GET, POST, headers, cookies, files  
- **Router** – handles routing, controllers, and middleware execution

This layout ensures Base is modular and extensible.
