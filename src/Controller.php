<?php

namespace Stilmark\Base;

use Stilmark\Base\Request;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
        $this->initialize();
    }

    /**
     * Initialize method that can be overridden by child classes
     */
    protected function initialize(): void
    {
        // Can be overridden by child classes
    }

    /**
     * Helper method to return JSON responses
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to a different URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }
}
