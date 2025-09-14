# Environment Keys

The `Env` class loads configuration values from your `.env` file.

### Common Keys

```
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=UTC
```

### Authentication Keys

```
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-app.com/oauth/callback
```

### Adding Custom Keys

You can define your own keys in `.env`:

```
API_URL=https://api.example.com
FEATURE_FLAG=true
```

Access them in code:

```php
use Stilmark\Base\Env;

$apiUrl = Env::get('API_URL');
```
