# CORS Setup

Configure Cross-Origin Resource Sharing (CORS) to allow your API to be accessed from different domains.

## Basic CORS Setup

### 1. Enable CORS in Environment

Add CORS configuration to your `.env` file:

```bash
# Enable CORS
CORS_ENABLED=true

# Allow specific origins
CORS_ALLOWED_ORIGINS=https://baseapp.com,https://baseapp.dev

# Configure allowed methods and headers
CORS_ALLOWED_METHODS=GET, POST, PUT, DELETE, OPTIONS
CORS_ALLOWED_HEADERS=Content-Type, Authorization, X-Requested-With

# Enable credentials if needed
CORS_ALLOW_CREDENTIALS=true

# Cache preflight for 24 hours
CORS_MAX_AGE=86400
```

### 2. Router Handles CORS Automatically

The Router automatically handles CORS when enabled. No additional code needed:

```php
<?php
require 'vendor/autoload.php';

use Stilmark\Base\Env;
use Stilmark\Base\Router;

// Load environment (includes CORS config)
Env::load(__DIR__ . '/.env');

// Router handles CORS preflight automatically
$router = new Router();
$router->dispatch();
```

## CORS Configuration Examples

### Development Setup

Allow localhost and development domains:

```bash
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080,https://dev.example.com
CORS_ALLOWED_METHODS=GET, POST, PUT, DELETE, OPTIONS
CORS_ALLOWED_HEADERS=Content-Type, Authorization, X-Requested-With, X-Debug-Token
CORS_ALLOW_CREDENTIALS=true
CORS_MAX_AGE=3600
```

### Production Setup

Restrict to specific production domains:

```bash
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
CORS_ALLOWED_METHODS=GET, POST, PUT, DELETE, OPTIONS
CORS_ALLOWED_HEADERS=Content-Type, Authorization
CORS_ALLOW_CREDENTIALS=true
CORS_MAX_AGE=86400
```

### Wildcard Subdomain Setup

Allow all subdomains of a domain:

```bash
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=https://*.example.com
CORS_ALLOWED_METHODS=GET, POST, OPTIONS
CORS_ALLOWED_HEADERS=Content-Type, Authorization
CORS_ALLOW_CREDENTIALS=false
CORS_MAX_AGE=86400
```

## Frontend Integration

### JavaScript Fetch API

```javascript
// Simple request (no preflight needed)
fetch('https://api.example.com/users', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data));

// Complex request (triggers preflight)
fetch('https://api.example.com/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer token123'
    },
    credentials: 'include', // Send cookies
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Axios Configuration

```javascript
// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Make requests
axios.post('https://api.example.com/users', {
    name: 'John Doe',
    email: 'john@example.com'
}, {
    headers: {
        'Authorization': 'Bearer token123'
    }
})
.then(response => console.log(response.data));
```

## CORS Headers Explained

### Response Headers Set by Base

When CORS is enabled, Base automatically sets these headers:

```http
Access-Control-Allow-Origin: https://baseapp.com
Vary: Origin
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

### Preflight Request Flow

1. **Browser sends OPTIONS request:**
```http
OPTIONS /api/users HTTP/1.1
Origin: https://baseapp.com
Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type, Authorization
```

2. **Base responds with CORS headers:**
```http
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: https://baseapp.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Max-Age: 86400
```

3. **Browser makes actual request:**
```http
POST /api/users HTTP/1.1
Origin: https://baseapp.com
Content-Type: application/json
Authorization: Bearer token123
```

## Security Considerations

### Origin Validation

Always specify exact origins in production:

```bash
# Good: Specific origins
CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com

# Avoid: Wildcard in production
CORS_ALLOWED_ORIGINS=*
```

### Credentials Handling

Only enable credentials when necessary:

```bash
# Enable only if you need cookies/auth headers
CORS_ALLOW_CREDENTIALS=true

# Disable for public APIs
CORS_ALLOW_CREDENTIALS=false
```

### Header Restrictions

Limit allowed headers to what you actually need:

```bash
# Minimal headers for simple APIs
CORS_ALLOWED_HEADERS=Content-Type

# Extended headers for complex apps
CORS_ALLOWED_HEADERS=Content-Type, Authorization, X-Requested-With, X-API-Key
```

## Troubleshooting CORS

### Common Issues

1. **CORS not working:**
   - Check `CORS_ENABLED=true` in `.env`
   - Verify origin is in `CORS_ALLOWED_ORIGINS`
   - Ensure no trailing slashes in origins

2. **Preflight failing:**
   - Check `OPTIONS` is in `CORS_ALLOWED_METHODS`
   - Verify all request headers are in `CORS_ALLOWED_HEADERS`

3. **Credentials not working:**
   - Set `CORS_ALLOW_CREDENTIALS=true`
   - Cannot use wildcard origin with credentials
   - Frontend must set `credentials: 'include'`

### Debug CORS Issues

Add debug logging to see CORS processing:

```php
// In development environment
if (Env::get('MODE') === 'DEVELOPMENT') {
    error_log('CORS Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'none'));
    error_log('CORS Enabled: ' . Env::get('CORS_ENABLED', 'false'));
    error_log('Allowed Origins: ' . Env::get('CORS_ALLOWED_ORIGINS', ''));
}
```

### Browser Developer Tools

Check the Network tab for:
- Preflight OPTIONS request
- CORS headers in response
- Console errors about CORS policy

## Testing CORS

### Manual Testing with cURL

Test preflight request:

```bash
curl -X OPTIONS \
  -H "Origin: https://baseapp.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  -v \
  https://api.example.com/users
```

Test actual request:

```bash
curl -X POST \
  -H "Origin: https://baseapp.com" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer token123" \
  -d '{"name":"John","email":"john@example.com"}' \
  -v \
  https://api.example.com/users
```

### Automated Testing

```php
public function testCorsHeaders()
{
    Env::set('CORS_ENABLED', 'true');
    Env::set('CORS_ALLOWED_ORIGINS', 'https://baseapp.com');
    
    $_SERVER['HTTP_ORIGIN'] = 'https://baseapp.com';
    $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
    
    ob_start();
    Router::dispatch();
    $output = ob_get_clean();
    
    $this->assertEquals(204, http_response_code());
    $this->assertStringContainsString('Access-Control-Allow-Origin: https://baseapp.com', $output);
}
```
