# Session-Based Authentication - Complete Example

This example demonstrates a complete session-based authentication implementation using Stilmark Base's Session, Auth, AuthMiddleware, and CsrfMiddleware classes.

## Table of Contents

1. [Application Structure](#application-structure)
2. [Bootstrap & Configuration](#bootstrap--configuration)
3. [Login Flow](#login-flow)
4. [Protected Routes](#protected-routes)
5. [CSRF Protection](#csrf-protection)
6. [Logout](#logout)
7. [Complete Code](#complete-code)

---

## Application Structure

```
/your-app
├── public/
│   └── index.php           # Entry point
├── src/
│   ├── bootstrap.php       # Application bootstrap
│   └── Middleware/
│       └── AppAuthMiddleware.php  # Custom auth middleware
├── .env                    # Environment configuration
└── composer.json
```

---

## Bootstrap & Configuration

### `.env` Configuration

```env
# Server
SERVER_NAME=example.com
APP_ENV=production

# Session
SESSION_AUTH_NAME=auth
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_SAMESITE=Strict
SESSION_IDLE_TIMEOUT=1800      # 30 minutes
SESSION_ABSOLUTE_TIMEOUT=28800 # 8 hours

# Google OAuth
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=/auth/google/callback

# CSRF
CSRF_ALLOWED_ORIGINS=https://example.com,https://www.example.com
```

### `src/bootstrap.php`

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Stilmark\Base\Env;
use Stilmark\Base\Session;

// Load environment variables
Env::load(__DIR__ . '/../.env');

// Configure session security
Session::configure([
    'cookie_secure' => Env::get('SESSION_COOKIE_SECURE', false),
    'cookie_httponly' => true,
    'cookie_samesite' => Env::get('SESSION_COOKIE_SAMESITE', 'Lax'),
    'use_strict_mode' => true,
    'name' => Env::get('SESSION_AUTH_NAME', 'auth') . '_SESSION',
    'gc_maxlifetime' => (int) Env::get('SESSION_ABSOLUTE_TIMEOUT', 28800),
]);

// Start session
session_start();
```

---

## Login Flow

### Login Route Handler

```php
<?php

use Stilmark\Base\Auth;
use Stilmark\Base\Request;
use Stilmark\Base\Render;
use Stilmark\Base\Session;

// Route: GET /login
function loginPage(Request $request)
{
    // Generate CSRF token for the login form
    $csrfToken = $request->generateCsrfToken();
    
    // Get flash messages
    $error = Session::getFlash('error');
    $success = Session::getFlash('success');
    
    // Render login page (example with inline HTML)
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <h1>Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <h2>Login with Google</h2>
        <form method="POST" action="/auth/google">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <button type="submit">Sign in with Google</button>
        </form>
    </body>
    </html>
    <?php
}

// Route: POST /auth/google (initiate OAuth)
function initiateGoogleAuth(Request $request)
{
    // Validate CSRF token
    if (!$request->validateCsrfToken()) {
        Session::flash('error', 'Invalid CSRF token');
        header('Location: /login');
        exit;
    }
    
    $auth = new Auth('google');
    $auth->callout(); // Redirects to Google
}

// Route: GET /auth/google/callback
function handleGoogleCallback(Request $request)
{
    $auth = new Auth('google');
    
    try {
        $result = $auth->callback($request);
        
        if ($result['status'] === 'success') {
            // Session is already set up by Auth::callback()
            // with regenerated session ID and timestamps
            
            Session::flash('success', 'Login successful!');
            header('Location: /dashboard');
            exit;
        } else {
            Session::flash('error', $result['message'] ?? 'Login failed');
            header('Location: /login');
            exit;
        }
    } catch (Exception $e) {
        Session::flash('error', 'Authentication error: ' . $e->getMessage());
        header('Location: /login');
        exit;
    }
}
```

---

## Protected Routes

### Custom Auth Middleware with Database Validation

```php
<?php
// src/Middleware/AppAuthMiddleware.php

namespace App\Middleware;

use Stilmark\Base\AuthMiddleware;
use Stilmark\Base\Env;

class AppAuthMiddleware extends AuthMiddleware
{
    private $db;
    
    public function __construct($db = null)
    {
        parent::__construct(
            authSessionKey: Env::get('SESSION_AUTH_NAME', 'auth'),
            idleTimeout: (int) Env::get('SESSION_IDLE_TIMEOUT', 1800),
            absoluteTimeout: (int) Env::get('SESSION_ABSOLUTE_TIMEOUT', 28800)
        );
        
        $this->db = $db;
    }
    
    /**
     * Custom validation: Check if user exists and is active
     */
    protected function validateSession(array $sessionData): bool
    {
        // First, call parent validation
        if (!parent::validateSession($sessionData)) {
            return false;
        }
        
        // If no database connection, skip database validation
        if (!$this->db) {
            return true;
        }
        
        // Get user email from session
        $userEmail = $sessionData['user']['email'] ?? null;
        if (!$userEmail) {
            return false;
        }
        
        // Check if user exists and is active in database
        $stmt = $this->db->prepare("SELECT id, status FROM users WHERE email = ?");
        $stmt->execute([$userEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || $user['status'] !== 'active') {
            $this->clearAuthSession();
            return false;
        }
        
        return true;
    }
}
```

### Protected Route Example

```php
<?php

use Stilmark\Base\Request;
use Stilmark\Base\Render;
use Stilmark\Base\Session;
use App\Middleware\AppAuthMiddleware;

// Route: GET /dashboard
function dashboard(Request $request, $db)
{
    // Create auth middleware with database connection
    $authMiddleware = new AppAuthMiddleware($db);
    
    // Validate authentication
    if (!$authMiddleware->handle()) {
        Session::flash('error', 'Please login to continue');
        header('Location: /login');
        exit;
    }
    
    // Get user data from session
    $authData = Session::get('auth');
    $user = $authData['user'] ?? null;
    
    // Generate CSRF token for forms on this page
    $csrfToken = $request->generateCsrfToken();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Dashboard</title>
    </head>
    <body>
        <h1>Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?>!</h1>
        
        <p>Email: <?= htmlspecialchars($user['email'] ?? '') ?></p>
        
        <h2>Session Info</h2>
        <ul>
            <li>Provider: <?= htmlspecialchars($authData['provider'] ?? 'N/A') ?></li>
            <li>Login Time: <?= date('Y-m-d H:i:s', $authData['auth_time'] ?? 0) ?></li>
            <li>Last Activity: <?= date('Y-m-d H:i:s', Session::get('last_activity', 0)) ?></li>
        </ul>
        
        <form method="POST" action="/logout">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <button type="submit">Logout</button>
        </form>
    </body>
    </html>
    <?php
}
```

---

## CSRF Protection

### Using CsrfMiddleware with Router

```php
<?php

use Stilmark\Base\Router;
use Stilmark\Base\CsrfMiddleware;
use Stilmark\Base\Env;

$router = new Router();

// Create CSRF middleware
$allowedOrigins = explode(',', Env::get('CSRF_ALLOWED_ORIGINS', ''));
$csrfMiddleware = new CsrfMiddleware($allowedOrigins);

// Apply CSRF protection to all POST/PUT/PATCH/DELETE routes
$router->post('/auth/google', 'initiateGoogleAuth', [$csrfMiddleware]);
$router->post('/logout', 'handleLogout', [$csrfMiddleware]);
$router->post('/api/users', 'createUser', [$csrfMiddleware]);
$router->delete('/api/users/:id', 'deleteUser', [$csrfMiddleware]);
```

### Manual CSRF Validation

```php
<?php

use Stilmark\Base\Request;
use Stilmark\Base\Render;

function createUser(Request $request)
{
    // Manual CSRF validation (if not using middleware)
    if ($request->isUnsafeMethod() && !$request->validateCsrfToken()) {
        Render::json(['error' => 'CSRF validation failed'], 403);
        exit;
    }
    
    // Process user creation
    $data = $request->json();
    // ... create user logic
    
    Render::json(['success' => true, 'user_id' => 123]);
}
```

### JavaScript API Calls with CSRF

```html
<!DOCTYPE html>
<html>
<head>
    <title>API Example</title>
</head>
<body>
    <button id="createUserBtn">Create User</button>
    
    <script>
        // Get CSRF token from meta tag or data attribute
        const csrfToken = '<?= $request->generateCsrfToken() ?>';
        
        document.getElementById('createUserBtn').addEventListener('click', async () => {
            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: 'John Doe',
                        email: 'john@example.com'
                    })
                });
                
                const data = await response.json();
                console.log('User created:', data);
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
```

---

## Logout

### Logout Handler

```php
<?php

use Stilmark\Base\Auth;
use Stilmark\Base\Request;
use Stilmark\Base\Session;

// Route: POST /logout
function handleLogout(Request $request)
{
    // Validate CSRF token
    if (!$request->validateCsrfToken()) {
        Session::flash('error', 'Invalid CSRF token');
        header('Location: /dashboard');
        exit;
    }
    
    $auth = new Auth();
    $auth->logout(); // Destroys session completely
    
    // Redirect to login page
    header('Location: /login?logged_out=1');
    exit;
}
```

---

## Complete Code

### `public/index.php` - Full Application

```php
<?php

require __DIR__ . '/../src/bootstrap.php';

use Stilmark\Base\Router;
use Stilmark\Base\Request;
use Stilmark\Base\Render;
use Stilmark\Base\Auth;
use Stilmark\Base\Session;
use Stilmark\Base\CsrfMiddleware;
use Stilmark\Base\Env;
use App\Middleware\AppAuthMiddleware;

// Initialize router
$router = new Router();

// Initialize database (example with PDO)
$db = new PDO(
    'mysql:host=localhost;dbname=myapp',
    Env::get('DB_USER'),
    Env::get('DB_PASS')
);

// Create middleware instances
$allowedOrigins = array_filter(explode(',', Env::get('CSRF_ALLOWED_ORIGINS', '')));
$csrfMiddleware = new CsrfMiddleware($allowedOrigins);
$authMiddleware = new AppAuthMiddleware($db);

// ============================================
// Public Routes
// ============================================

$router->get('/', function() {
    header('Location: /login');
    exit;
});

$router->get('/login', function(Request $request) {
    $csrfToken = $request->generateCsrfToken();
    $error = Session::getFlash('error');
    $success = Session::getFlash('success');
    
    include __DIR__ . '/../views/login.php';
});

// ============================================
// OAuth Routes (with CSRF protection)
// ============================================

$router->post('/auth/google', function(Request $request) {
    $auth = new Auth('google');
    $auth->callout();
}, [$csrfMiddleware]);

$router->get('/auth/google/callback', function(Request $request) {
    $auth = new Auth('google');
    
    try {
        $result = $auth->callback($request);
        
        if ($result['status'] === 'success') {
            Session::flash('success', 'Welcome back!');
            header('Location: /dashboard');
        } else {
            Session::flash('error', $result['message'] ?? 'Login failed');
            header('Location: /login');
        }
    } catch (Exception $e) {
        Session::flash('error', 'Authentication error');
        header('Location: /login');
    }
    exit;
});

// ============================================
// Protected Routes (with Auth + CSRF)
// ============================================

$router->get('/dashboard', function(Request $request) use ($authMiddleware) {
    if (!$authMiddleware->handle()) {
        Session::flash('error', 'Please login to continue');
        header('Location: /login');
        exit;
    }
    
    $authData = Session::get('auth');
    $user = $authData['user'] ?? null;
    $csrfToken = $request->generateCsrfToken();
    
    include __DIR__ . '/../views/dashboard.php';
});

$router->post('/logout', function(Request $request) use ($authMiddleware) {
    if (!$authMiddleware->handle()) {
        header('Location: /login');
        exit;
    }
    
    $auth = new Auth();
    $auth->logout();
    
    header('Location: /login?logged_out=1');
    exit;
}, [$csrfMiddleware]);

// ============================================
// API Routes (with Auth + CSRF)
// ============================================

$router->get('/api/profile', function(Request $request) use ($authMiddleware) {
    if (!$authMiddleware->handle()) {
        Render::json(['error' => 'Unauthorized'], 401);
        exit;
    }
    
    $authData = Session::get('auth');
    Render::json([
        'user' => $authData['user'] ?? null,
        'provider' => $authData['provider'] ?? null
    ]);
}, [$authMiddleware]);

$router->post('/api/users', function(Request $request) use ($authMiddleware, $db) {
    if (!$authMiddleware->handle()) {
        Render::json(['error' => 'Unauthorized'], 401);
        exit;
    }
    
    $data = $request->json();
    
    // Validate input
    if (!isset($data['name']) || !isset($data['email'])) {
        Render::json(['error' => 'Missing required fields'], 400);
        exit;
    }
    
    // Create user in database
    $stmt = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['email']]);
    
    Render::json([
        'success' => true,
        'user_id' => $db->lastInsertId()
    ]);
}, [$authMiddleware, $csrfMiddleware]);

// ============================================
// Dispatch Router
// ============================================

$router->dispatch();
```

---

## Security Checklist

✅ **Session Security**
- HttpOnly cookies enabled
- Secure flag enabled (HTTPS only)
- SameSite attribute set
- Session ID regenerated after login
- Complete session destruction on logout

✅ **CSRF Protection**
- CSRF tokens on all forms
- CSRF validation on all unsafe methods
- Time-bucketed tokens with rotation
- Origin/Referer validation

✅ **Timeout Management**
- Idle timeout (30 minutes default)
- Absolute timeout (8 hours default)
- Automatic activity tracking
- Session cleanup on timeout

✅ **Authentication**
- OAuth2 state validation
- Custom session validation
- Database user status checks
- Protected route enforcement

---

## Testing the Application

### 1. Test Login Flow
```bash
# Visit login page
curl -c cookies.txt http://localhost/login

# Initiate OAuth (will redirect to Google)
curl -b cookies.txt -X POST http://localhost/auth/google \
  -d "_token=YOUR_CSRF_TOKEN"
```

### 2. Test Protected Routes
```bash
# Access dashboard without auth (should redirect)
curl -L http://localhost/dashboard

# Access dashboard with session
curl -b cookies.txt http://localhost/dashboard
```

### 3. Test CSRF Protection
```bash
# POST without CSRF token (should fail)
curl -b cookies.txt -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'

# POST with CSRF token (should succeed)
curl -b cookies.txt -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -d '{"name":"John","email":"john@example.com"}'
```

### 4. Test Timeout
```bash
# Login and wait for idle timeout (30 minutes)
# Then try to access protected route (should redirect to login)
```

---

## See Also

- [Session](../core/session.md) - Session management utilities
- [Auth](../core/auth.md) - OAuth2 authentication
- [AuthMiddleware](../core/auth-middleware.md) - Authentication middleware
- [CsrfMiddleware](../core/csrf-middleware.md) - CSRF protection
- [Request](../core/request.md) - Request handling and CSRF tokens
