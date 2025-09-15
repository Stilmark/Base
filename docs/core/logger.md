# Logger

Centralized logging functionality with Rollbar integration for error tracking and monitoring.

**Repository:** [rollbar/rollbar-php](https://github.com/rollbar/rollbar-php)

## Environment Configuration

```
LOG_API=ROLLBAR
LOG_API_TOKEN=your_rollbar_access_token
LOG_PATH=/logs
```

## Rollbar API Key Setup

To obtain a Rollbar API key:

1. Go to [Rollbar.com](https://rollbar.com/) and create an account
2. Create a new project or select an existing one
3. Navigate to **Settings** → **Project Access Tokens**
4. Copy the **post_server_item** token
5. Add it to your `.env` file as `LOG_API_TOKEN`

## API

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

- **Automatic user context**: Includes session user data when available
- **Level validation**: Invalid levels default to 'info'
- **Rollbar integration**: Seamless error tracking and monitoring
- **Environment-based**: Only logs to Rollbar when `LOG_API=ROLLBAR`

## Usage Examples

```php
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

This enables user-specific error tracking in Rollbar.
