<?php

include_once 'init.php';

use Stilmark\Base\Env;

echo Env::get('MODE').PHP_EOL;

Env::set('MODE', 'DEMO');

echo Env::get('MODE').PHP_EOL;

echo Env::get('LOCALE').PHP_EOL;

