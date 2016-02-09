<?php

date_default_timezone_set('America/Chicago');

if(file_exists(__DIR__ ."/../vendor/autoload.php")) {
	require_once(__DIR__ ."/../vendor/autoload.php");
}
else {
	trigger_error("vendor autoloader not found, unit tests will probably fail -- try running 'composer update'");
}

define('UNITTEST__LOCKFILE', __DIR__ .'/files/rw');

