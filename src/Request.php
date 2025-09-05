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

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->getAllHeaders();
        $this->input = json_decode(file_get_contents('php://input'), true) ?? [];
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
     * Get JSON input
     */
    public function json(string $key = null, $default = null)
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
