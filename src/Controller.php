<?php

namespace Stilmark\Base;

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
        Render::json($data, $statusCode);
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
