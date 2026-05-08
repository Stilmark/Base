# Logger

File-based JSON logging with automatic user context.

## Environment Configuration

```
LOG_PATH=/logs
```

The log directory is created automatically with `0775` permissions (group read/write) if it doesn't exist.

## API

### `Logger::init(): void`

Initializes error reporting and file-based logging. Call early in bootstrap.

```php
Logger::init();
```

- Creates log directory (0775) if needed
- Configures PHP error reporting (all errors)
- Displays errors only when `DEV` constant is true
- Redirects PHP errors to `php_errors.log`

### `Logger::log(string $message, string $level, array $data, string $filename): bool`

```php
Logger::log(
    string $message = 'log',
    string $level = 'info',
    array $data = [],
    string $filename = 'app.log'
)
```

**Parameters:**
- `$message` - Log message
- `$level` - Log level (see below)
- `$data` - Additional context data
- `$filename` - Log filename (default: `app.log`)

### Log Levels

`debug` | `info` | `notice` | `warning` | `error` | `critical` | `alert` | `emergency`

Invalid levels default to `info`.

## Log Files

- **`/logs/app.log`** - Default application log
- **`/logs/php_errors.log`** - PHP errors, warnings, and notices
- **`/logs/{custom}.log`** - Custom log files via `$filename` parameter

## Log Format

Each entry is a single-line JSON object:

```json
{"timestamp":"2025-09-16 14:30:00","level":"INFO","message":"User login","data":{"user_id":123}}
```

## Examples

```php
Logger::init();

// Basic
Logger::log('User login successful');

// With level and data
Logger::log('Payment processed', 'info', [
    'amount' => 99.99,
    'transaction_id' => 'txn_123'
]);

// Error logging
Logger::log('Database connection failed', 'error', [
    'host' => 'db.example.com'
]);

// Custom log file
Logger::log('Order created', 'info', ['order_id' => 456], 'orders.log');
Logger::log('Email sent', 'info', ['to' => 'user@example.com'], 'mail.log');
```

## User Context

When `$_SESSION['user']` exists, the logger automatically appends:

```json
{"person": {"id": 123, "email": "user@example.com"}}
```
