<?php
use FastRoute\RouteCollector;

/** @var RouteCollector $r */

// Public routes (no authentication required)
$r->addGroup('/signin', function (RouteCollector $r) {    
    $r->addRoute('GET', '/google/callout', 'AuthController@callout');
    $r->addRoute('GET', '/google/callback', 'AuthController@callback');
});

// Protected API routes (authentication required)
$r->addGroup('/api', function (RouteCollector $r) {
    $r->addRoute('GET', '/user/{id:\d+}', 'UserController@index');
}, ['middlewares' => [AuthMiddleware::class]]);