<?php

ini_set('error_reporting', E_ALL );

define('ROOT', __DIR__.'/..');
require(ROOT . '/vendor/autoload.php');

use Stilmark\Base\Env;

Env::load(__DIR__ . '/.env');
