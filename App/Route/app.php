<?php

use Sys\Utils\Router;

// usage : uri=>controller::function
// example : 'hello' => 'DemoController::hello', uri=hello
// example : 'hello' => ['john'=>DemoController::hello'], uri=hello/john

//set controller root namespace
Router::setControllerRootNamespace('\App\Controller');

//simple map
Router::add([
	'hello' => 'DemoController::hello',
	'article' => [
		'list' => 'DemoController::hello',
	],
]);

// use middleware or controller namespace
Router::add(['middleware' => 'DemoMiddleware', 'namespace' => ''], [
	'user' => [
		'info' => 'DemoController::hello',
	],
]);