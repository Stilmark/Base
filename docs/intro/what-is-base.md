# What is Base

The **Base Class** is a lightweight PHP utility library providing common functionality used across PHP applications.

It includes helpers and core components for:

- **Environment Management**: Load and access `.env` variables.
- **Request Handling**: A unified interface for HTTP requests, including input retrieval, validation, and sanitization.
- **Routing**: A simple and fast router with middleware support.
- **Controllers**: A base controller to extend for application logic.
- **Response Rendering**: Helpers for sending JSON and CSV responses.
- **Authentication**: Multi-provider OAuth2 support (e.g., Google) and middleware for protecting routes.
- **Logging**: PSR-3 compliant logging with built-in Rollbar integration.
- **Helper Utilities**: Static methods for common tasks like string manipulation.

Base is designed to be used directly or as the foundation for [BaseApp](https://github.com/Stilmark/BaseApp).
