<?php

declare(strict_types=1);

namespace Stilmark\Base;

use Stilmark\Base\Env;
use Stilmark\Base\AuthMiddleware;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;

use ReflectionMethod;
use ReflectionNamedType;
use Throwable;
use JsonSerializable;

class Router {

    public static function dispatch()
    {
        // ----------------------------------------
        // Handle CORS preflight requests
        // ----------------------------------------
        self::handleCors();

        $dispatcher = cachedDispatcher(
            function (RouteCollector $r) {
                require ROOT . Env::get('ROUTES_PATH');
            },
            [
                'cacheFile'     => ROOT . Env::get('ROUTES_CACHE_PATH'),
                'cacheDisabled' => defined('DEV') && DEV,
            ]
        );

        // ----------------------------------------
        // Dispatch
        // ----------------------------------------

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $routeInfo = $dispatcher->dispatch($httpMethod, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                Render::json(['error' => 'Not Found'], 404);

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowed = $routeInfo[1] ?? [];
                if ($allowed) header('Allow: ' . implode(', ', (array)$allowed));
                Render::json(['error' => 'Method Not Allowed'], 405);

            case Dispatcher::FOUND:
                $handlerData = $routeInfo[1];
                if (is_array($handlerData)) {
                    $handler = (string)$handlerData[0];
                    $routeData = $handlerData[1] ?? [];
                } else {
                    $handler = (string)$handlerData;
                    $routeData = $routeInfo[3] ?? [];
                }
                $vars    = (array)$routeInfo[2];          // associative: paramName => value
                $middlewares = $routeData['middlewares'] ?? [];

                // Run middlewares
                if (!empty($middlewares) && !Router::runMiddlewares($middlewares)) {
                    Render::json(['error' => 'Unauthorized'], 401);
                }

                try {
                    [$class, $method] = Router::resolveHandler($handler);

                    // Instantiate controller (swap for your DI container if desired)
                    $controller = new $class();

                    // Bind & cast by signature, in parameter order
                    $args = Router::bindAndCastArgs($controller, $method, $vars);

                    $result = $controller->{$method}(...$args);

                    // Ensure array payload for JSON; wrap scalars/objects as needed
                    if (!is_array($result)) {
                        // Convert JsonSerializable to array automatically
                        if ($result instanceof JsonSerializable) {
                            $result = $result->jsonSerialize();
                        } else {
                            $result = ['data' => $result];
                        }
                    }

                    // Check if we're in development mode for pretty printing
                    $prettyPrint = defined('DEV') && DEV;
                    Render::json($result, 200, $prettyPrint);

                } catch (Throwable $e) {

                    $error = [
                        'error'   => 'Server Error',
                        'message' => $e->getMessage(),
                    ];

                    // Output error in development mode
                    if (defined('DEV') && DEV) {
                        $error['trace'] = $e->getTraceAsString();
                    }

                    Render::json($error, 500);
                }
        }

    }

    // ----------------------------------------
    // Middleware support
    // ----------------------------------------
    private static function runMiddlewares(array $middlewares): bool {
        foreach ($middlewares as $middleware) {
            $middlewareInstance = new $middleware();
            if (method_exists($middlewareInstance, 'handle')) {
                if (!$middlewareInstance->handle()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    // ----------------------------------------
    // Resolve handler
    // ----------------------------------------
    private static function resolveHandler(string $handler): array {
        [$classShort, $method] = explode('@', $handler) + [null, null];
        if (!$classShort || !$method) {
            Render::json(['error' => 'Invalid handler'], 500);
        }
        $class = Env::get('CONTROLLER_NS') . ltrim($classShort, '\\');
        
        
        if (!class_exists($class) || !method_exists($class, $method)) {
            Render::json(['error' => 'Handler not found'], 500);
        }
        return [$class, $method];
    }

    // ----------------------------------------
    // Bind & cast route vars using method signature
    // Supports: int, float, bool, string, array, ?type (nullable)
    // Unrecognized/union types are passed through as-is.
    // ----------------------------------------

    private static function bindAndCastArgs(object $obj, string $method, array $vars): array {
        $rm = new ReflectionMethod($obj, $method);
        $params = $rm->getParameters();
    
        $out = [];
        foreach ($params as $p) {
            $name = $p->getName();
    
            // Match by parameter name; fall back to default/null if not present
            $val = array_key_exists($name, $vars)
                ? $vars[$name]
                : ($p->isDefaultValueAvailable() ? $p->getDefaultValue() : null);
    
            $type = $p->getType();
    
            if ($type instanceof ReflectionNamedType) {
                $tName   = $type->getName();
                $nullable = $type->allowsNull();
    
                if ($val === null) {
                    $out[] = null;
                    continue;
                }
    
                switch ($tName) {
                    case 'int':
                        if (is_string($val) && ctype_digit($val)) $val = (int)$val;
                        elseif (is_numeric($val)) $val = (int)$val;
                        break;
    
                    case 'float':
                        if (is_numeric($val)) $val = (float)$val;
                        break;
    
                    case 'bool':
                        // Accept 1/0/true/false/on/off/yes/no
                        $tmp = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($tmp === null) $tmp = false;
                        $val = $tmp;
                        break;
    
                    case 'string':
                        $val = (string)$val;
                        break;
    
                    case 'array':
                        // If a CSV slipped in, you could explode here; default: pass-through
                        if (is_string($val) && str_contains($val, ',')) {
                            // $val = array_map('trim', explode(',', $val));
                        }
                        break;
    
                    case 'object':
                    default:
                        // Leave as-is for unsupported scalars/objects/union/intersection
                        break;
                }
            } else {
                // Union/intersection types: pass through without casting
                // (You can enhance with ReflectionUnionType if you want.)
            }
    
            // If still null & not nullable with no default, keep null (PHP will error at call time)
            if ($val === null && !$p->allowsNull() && !$p->isDefaultValueAvailable()) {
                // You can decide to error early instead:
                // jsonResponse(['error' => "Missing required parameter '$name'"], 400);
            }
    
            $out[] = $val;
        }
    
        return $out;
    }

    // ----------------------------------------
    // CORS handling
    // ----------------------------------------
    private static function handleCors(): void
    {
        // Check if CORS is enabled
        if (Env::get('CORS_ENABLED', 'false') !== 'true') {
            return;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = self::getAllowedOrigins();
        
        // Check if origin is allowed
        if (self::isOriginAllowed($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Vary: Origin");
            
            // Add credentials support if enabled
            if (Env::get('CORS_ALLOW_CREDENTIALS', 'false') === 'true') {
                header("Access-Control-Allow-Credentials: true");
            }
        }

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $allowedMethods = Env::get('CORS_ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
            $allowedHeaders = Env::get('CORS_ALLOWED_HEADERS', 'Content-Type, Authorization, X-Requested-With');
            $maxAge = Env::get('CORS_MAX_AGE', '86400'); // 24 hours default

            header("Access-Control-Allow-Methods: $allowedMethods");
            header("Access-Control-Allow-Headers: $allowedHeaders");
            header("Access-Control-Max-Age: $maxAge");
            
            http_response_code(204);
            exit;
        }
    }

    /**
     * Get allowed origins from environment configuration
     */
    private static function getAllowedOrigins(): array
    {
        $origins = Env::get('CORS_ALLOWED_ORIGINS', '');
        
        if (empty($origins)) {
            return [];
        }
        
        // Handle wildcard
        if ($origins === '*') {
            return ['*'];
        }
        
        // Split comma-separated origins and trim whitespace
        return array_map('trim', explode(',', $origins));
    }

    /**
     * Check if the given origin is allowed
     */
    private static function isOriginAllowed(string $origin, array $allowedOrigins): bool
    {
        if (empty($origin) || empty($allowedOrigins)) {
            return false;
        }
        
        // Allow all origins if wildcard is set
        if (in_array('*', $allowedOrigins)) {
            return true;
        }
        
        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }
        
        // Check pattern matching for subdomains (e.g., *.example.com)
        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($allowedOrigin, '*') !== false) {
                $pattern = str_replace('*', '.*', preg_quote($allowedOrigin, '/'));
                if (preg_match("/^$pattern$/", $origin)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}