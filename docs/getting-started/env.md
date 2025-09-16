# Environment Variables

- **Core Class:** [Env](../core/env.md)
- **Example:** [Using Env in Code](../examples/env-usage.md)

---

The `Env` class manages application configuration using environment variables.

### Loading variables

```php
use Stilmark\Base\Env;

Env::load(__DIR__ . '/../.env');
```

### Accessing values

```php
$timezone = Env::get('APP_TIMEZONE', 'UTC');
```

### Setting values at runtime

```php
Env::set('DEBUG', true);
```

### Typical .env file

```
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=UTC

GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-app.com/oauth/callback
```
