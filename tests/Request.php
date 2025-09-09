<?php

include_once 'init.php';

use Stilmark\Base\Request;
use Stilmark\Base\Render;

$_GET['name'] = 'John Doe';
$_POST['name'] = 'John Doe';
$_COOKIE['name'] = 'John Doe';
$_SERVER['HTTP_NAME'] = 'John Doe';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['CONTENT_LENGTH'] = '10';
$_FILES['name'] = [
    'name' => 'John Doe',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/johndoe.txt',
    'error' => 0,
    'size' => 10,
];

// Create a request with simulated JSON input for testing
$request = new Request(['name' => 'John Doe']);

Render::json([
    'all' => $request->all(),
    'get' => $request->get('name'),
    'post' => $request->post('name'),
    'json' => $request->json('name'),
    'server' => $request->server('SERVER_NAME'),
    'header' => $request->header('name'),
    'cookie' => $request->cookie('name'),
    'file' => $request->file('name')
], prettyPrint: true);

