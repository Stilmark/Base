<?php

include_once 'init.php';

use Stilmark\Base\Router;

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/list/staticVars';


$response = Router::dispatch();

print_r($response);