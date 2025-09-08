<?php

declare(strict_types=1);

namespace Stilmark\Base;

use Stilmark\Base\Env;
use Stilmark\Base\AuthMiddleware;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;

class Router {

    public static function dispatch()
    {

        die(ROOT . Env::get('ROUTES_CACHE_PATH'));

        $dispatcher = cachedDispatcher(
            function (RouteCollector $r) {
                require ROOT . Env::get('ROUTES_CACHE_PATH');
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
                $handler = (string)$routeInfo[1];
                $vars    = (array)$routeInfo[2];          // associative: paramName => value
                $middlewares = $routeInfo[3]['middlewares'] ?? [];

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

                    Render::json($result, 200);

                } catch (Throwable $e) {
                    Render::json([
                        'error'   => 'Server Error',
                        'message' => $e->getMessage(),
                    ], 500);
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

}