<?php

namespace Stilmark\Base;

final class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $headers;
    private array $cookies;
    private array $files;
    private $input;
    
    // Security constants
    private const MAX_JSON_SIZE = 1048576; // 1MB
    private const MAX_JSON_DEPTH = 512;

    public function __construct(?array $jsonInput = null)
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->getAllHeaders();
        
        // Allow injecting JSON input for testing, otherwise read from php://input
        if ($jsonInput !== null) {
            $this->input = $jsonInput;
        } else {
            $this->input = $this->parseJsonInput();
        }
    }

    /**
     * Get all request data (GET, POST, and JSON input)
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->input);
    }

    /**
     * Query a request parameter (GET takes precedence over POST)
     */
    public function query(string $key, $default = null)
    {
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (is_array($this->input) && array_key_exists($key, $this->input)) {
            return $this->input[$key];
        }
        return $default;
    }

    /**
     * Get a GET parameter
     */
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get a POST parameter
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Check if GET parameter(s) exist and return their values
     * @param string|array $keys Single key or array of keys to check
     * @return array|false Array of key-value pairs if all keys exist, false otherwise
     */
    public function hasGet(string|array $keys): array|false
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $result = [];
        
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->get)) {
                return false;
            }
            $result[$key] = $this->get[$key];
        }
        
        return $result;
    }

    /**
     * Check if POST parameter(s) exist and return their values
     * @param string|array $keys Single key or array of keys to check
     * @return array|false Array of key-value pairs if all keys exist, false otherwise
     */
    public function hasPost(string|array $keys): array|false
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $result = [];
        
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->post)) {
                return false;
            }
            $result[$key] = $this->post[$key];
        }
        
        return $result;
    }

    /**
     * Get JSON input
     */
    public function json(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->input;
        }
        return $this->input[$key] ?? $default;
    }

    /**
     * Get a server variable
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get a header value
     */
    public function header(string $key, $default = null)
    {
        $key = strtoupper(str_replace('-', '_', $key));
        $key = 'HTTP_' . $key;
        return $this->server($key, $default);
    }

    /**
     * Get the request method
     */
    public function method(): string
    {
        return $this->server('REQUEST_METHOD', 'GET');
    }

    /**
     * Get the request URI
     */
    public function uri(): string
    {
        return $this->server('REQUEST_URI', '');
    }

    /**
     * Check if the request is an AJAX request
     */
    public function isAjax(): bool
    {
        return !empty($this->server('HTTP_X_REQUESTED_WITH')) && 
               strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Get a cookie value
     *
     * @param string $key The cookie name
     * @param mixed $default Default value if cookie doesn't exist
     * @return mixed
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get an uploaded file
     *
     * @param string $key The file input name
     * @param mixed $default Default value if file doesn't exist
     * @return array|mixed
     */
    public function file(string $key, $default = null)
    {
        return $this->files[$key] ?? $default;
    }

    /**
     * Safely parse JSON input with size and depth limits
     */
    private function parseJsonInput(): array
    {
        $input = file_get_contents('php://input');
        
        // Check size limit
        if (strlen($input) > self::MAX_JSON_SIZE) {
            throw new \InvalidArgumentException('JSON input exceeds maximum size limit');
        }
        
        if (empty($input)) {
            return [];
        }
        
        $decoded = json_decode($input, true, self::MAX_JSON_DEPTH, JSON_THROW_ON_ERROR);
        
        if (!is_array($decoded)) {
            return [];
        }
        
        return $decoded;
    }
    
    /**
     * Sanitize string input to prevent XSS
     */
    public function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Get sanitized input (XSS protection)
     */
    public function safe(string $key, $default = null): string
    {
        $value = $this->query($key, $default);
        return is_string($value) ? $this->sanitize($value) : $default;
    }
    
    /**
     * Get and validate email input
     * @param string $key The input key to retrieve
     * @param string|null $default Default value if key doesn't exist or email is invalid
     * @return string|null Valid email address or default value
     */
    public function email(string $key, ?string $default = null): ?string
    {
        $value = $this->query($key, $default);
        
        if (!is_string($value)) {
            return $default;
        }
        
        // Sanitize and validate email
        $email = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return $default;
    }
    
    /**
     * Validate POST data with rules
     * @param array $rules Array of field => validation rules
     * @return array Validation result with 'valid', 'data', and 'errors' keys
     */
    public function validatePost(array $rules): array
    {
        return $this->validateData($this->post, $rules);
    }
    
    /**
     * Validate GET data with rules
     * @param array $rules Array of field => validation rules
     * @return array Validation result with 'valid', 'data', and 'errors' keys
     */
    public function validateGet(array $rules): array
    {
        return $this->validateData($this->get, $rules);
    }

    /**
     * Validate and sanitize file upload
     */
    public function validateFile(string $key, array $allowedTypes = [], int $maxSize = 5242880): array|false
    {
        $file = $this->file($key);
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        // Check file type if specified
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes, true)) {
                return false;
            }
        }
        
        // Sanitize filename
        $file['name'] = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        
        return $file;
    }

    /**
     * Internal validation logic
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Validation result with 'valid', 'data', and 'errors' keys
     */
    private function validateData(array $data, array $rules): array
    {
        $validated = [];
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldValid = true;
            
            // Check each rule for this field
            foreach ($fieldRules as $rule) {
                if ($rule === 'required') {
                    if ($value === null || $value === '') {
                        $errors[$field] = 'This field is required';
                        $fieldValid = false;
                        break;
                    }
                } elseif ($rule === 'email') {
                    if ($value !== null) {
                        $email = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = 'Invalid email format';
                            $fieldValid = false;
                            break;
                        }
                        $value = $email; // Use sanitized email
                    }
                } elseif (str_starts_with($rule, 'min:')) {
                    $minLength = (int) substr($rule, 4);
                    if ($value !== null && strlen($value) < $minLength) {
                        $errors[$field] = "Must be at least {$minLength} characters";
                        $fieldValid = false;
                        break;
                    }
                } elseif (str_starts_with($rule, 'max:')) {
                    $maxLength = (int) substr($rule, 4);
                    if ($value !== null && strlen($value) > $maxLength) {
                        $errors[$field] = "Must not exceed {$maxLength} characters";
                        $fieldValid = false;
                        break;
                    }
                } elseif ($rule === 'url') {
                    if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field] = 'Must be a valid URL';
                        $fieldValid = false;
                        break;
                    }
                } elseif (str_starts_with($rule, 'type:')) {
                    $type = substr($rule, 5);
                    if ($value !== null && !$this->validateType($value, $type)) {
                        $errors[$field] = "Must be a valid {$type}";
                        $fieldValid = false;
                        break;
                    }
                }
            }
            
            if ($fieldValid) {
                $validated[$field] = $value;
            }
        }
        
        return [
            'valid' => empty($errors),
            'data' => $validated,
            'errors' => $errors
        ];
    }
    
    /**
     * Validate value type
     */
    private function validateType($value, string $type): bool
    {
        return match($type) {
            'int', 'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'float', 'double' => filter_var($value, FILTER_VALIDATE_FLOAT) !== false,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'string' => is_string($value),
            default => true
        };
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $sessionTokenKey = 'csrf_token'): bool
    {
        $token = $this->query('_token') ?? $this->header('X-CSRF-TOKEN');
        
        if (!$token || !isset($_SESSION[$sessionTokenKey])) {
            return false;
        }
        
        return hash_equals($_SESSION[$sessionTokenKey], $token);
    }
    
    /**
     * Get all request headers
     */
    private function getAllHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
