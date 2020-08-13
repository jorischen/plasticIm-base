<?php

return array(
	'container' => [
		'register' => [
			'server' => ['bind' => '\Sys\Server', 'is_singleton' => true],
			'log' => '\Sys\Utils\Logger',
			'config' => '\Sys\Utils\Config',
			'route' => '\Sys\Utils\Router',
			'user_error_handler' => '\App\Exceptions\Handler',
			'error_handler' => '\Sys\Constraint\ExceptionHandler',
		],
	],
);