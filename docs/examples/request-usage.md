# Request Class Usage Examples

This document provides detailed examples for using the `Request` class.

## Basic Data Retrieval

```php
use Stilmark\Base\Request;

$request = new Request();

// URL: /users?id=123&name=John
$userId = $request->get('id'); // "123"
$name = $request->get('name', 'Guest'); // "John"
$age = $request->get('age', 30); // 30 (default)

// POST data: name=Jane&email=jane@example.com
$postName = $request->post('name'); // "Jane"

// JSON payload: {"isActive": true, "roles": ["admin", "editor"]}
$payload = $request->json(); // Returns the full array
$isActive = $request->json('isActive'); // true
$roles = $request->json('roles'); // ["admin", "editor"]
```

## Checking for Parameter Existence

The `hasGet()`, `hasPost()`, and `hasJson()` methods are useful for verifying that required parameters are present before processing them.

```php
// URL: /search?q=test&limit=10
if ($params = $request->hasGet(['q', 'limit'])) {
    // $params will be ['q' => 'test', 'limit' => '10']
    search($params['q'], $params['limit']);
} else {
    // Handle missing parameters
    echo 'Both `q` and `limit` are required.';
}

// JSON payload: {"username": "test", "password": "secret"}
if ($credentials = $request->hasJson(['username', 'password'])) {
    // Authenticate user
    login($credentials['username'], $credentials['password']);
}
```

## Validation

Validate input data from GET, POST, or JSON sources against a set of rules.

```php
// Validate a registration form (POST)
$rules = [
    'username' => ['required', 'min:3', 'max:20'],
    'email' => ['required', 'email'],
    'password' => ['required', 'min:8']
];

$validation = $request->validatePost($rules);

if ($validation['valid']) {
    // Data is valid, proceed with registration
    createUser($validation['data']);
} else {
    // Return errors to the user
    // $validation['errors'] will contain messages like:
    // ['email' => 'Invalid email format']
    displayErrors($validation['errors']);
}

// Validate JSON data
$jsonRules = [
    'productId' => ['required', 'type:int'],
    'quantity' => ['required', 'type:int', 'min:1']
];

$jsonValidation = $request->validateJson($jsonRules);

if ($jsonValidation['valid']) {
    addToCart($jsonValidation['data']);
}
```

## Accessing Request Metadata

```php
// Get the request method
if ($request->method() === 'POST') {
    // ...
}

// Get the Authorization header
$token = $request->header('Authorization');

// Get the User-Agent
$userAgent = $request->server('HTTP_USER_AGENT');

// Get a cookie
$sessionId = $request->cookie('SESSION_ID');

// Check if it's an AJAX request
if ($request->isAjax()) {
    // Return JSON response
}
```

## File Uploads

Handle and validate file uploads securely.

```php
$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 2 * 1024 * 1024; // 2MB

$file = $request->validateFile('avatar', $allowedTypes, $maxSize);

if ($file) {
    // File is valid and sanitized
    $destination = '/uploads/' . $file['name'];
    move_uploaded_file($file['tmp_name'], $destination);
    echo 'File uploaded successfully!';
} else {
    // Handle invalid file (wrong type, too large, etc.)
    echo 'Invalid file upload.';
}
```
