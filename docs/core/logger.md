# Logger

Centralized file-based logging functionality for error tracking and monitoring.

## Environment Configuration

```
LOG_PATH=/logs
```

The `LOG_PATH` should point to your project's logs directory. The logger will create this directory if it doesn't exist.

## API

### Logger::init()

Initializes error reporting and file-based logging. Should be called early in your application bootstrap.

```php
Logger::init();
```

This method:
- Creates the log directory if it doesn't exist
- Configures PHP error reporting
- Redirects PHP errors to `php_errors.log` in the log directory

### Logger::log()

```php
Logger::log(string $message, string $level = 'info', array $data = [])
```

### Supported Log Levels

- `debug` - Detailed debug information
- `info` - General information messages
- `notice` - Normal but significant events
- `warning` - Warning messages
- `error` - Error conditions
- `critical` - Critical conditions
- `alert` - Action must be taken immediately
- `emergency` - System is unusable

## Features

- **File-based logging**: All logs written to `app.log` in the configured log directory
- **JSON format**: Each log entry is a JSON object for easy parsing
- **Automatic user context**: Includes session user data when available
- **Level validation**: Invalid levels default to 'info'
- **PHP error capture**: PHP errors redirected to `php_errors.log`

## Log Files

When `LOG_PATH=/logs` is configured:

- **`/logs/app.log`** - Application logs from `Logger::log()` calls
- **`/logs/php_errors.log`** - PHP errors, warnings, and notices

## Log Format

Each log entry is a JSON object:

```json
{"timestamp":"2025-09-16 14:30:00","level":"INFO","message":"User login successful","data":{"user_id":123}}
```

## Usage Examples

```php
// Initialize logging
Logger::init();

// Basic logging
Logger::log('User login successful');

// With specific level
Logger::log('Database connection failed', 'error');

// With additional context data
Logger::log('Payment processed', 'info', [
    'amount' => 99.99,
    'currency' => 'USD',
    'transaction_id' => 'txn_123'
]);

// Critical system error
Logger::log('System out of memory', 'critical', [
    'memory_usage' => memory_get_usage(),
    'memory_limit' => ini_get('memory_limit')
]);
```

## User Context

When a user session exists (`$_SESSION['user']`), the logger automatically includes:

```php
[
    'person' => [
        'id' => $_SESSION['user']['id'],
        'email' => $_SESSION['user']['email']
    ]
]
```

This enables user-specific tracking in your log files.
