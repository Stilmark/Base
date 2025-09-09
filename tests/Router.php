<?php

include_once 'init.php';

use Stilmark\Base\Router;
use Stilmark\Base\Render;


$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/list/staticVars';

Router::dispatch();