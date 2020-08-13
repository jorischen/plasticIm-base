<?php

namespace App\Middleware;

use Closure;
use Sys\Constraint\MiddlewareInterface;

class DemoMiddleware implements MiddlewareInterface {

	public function handle($request, Closure $next) {

		//do something

		$response = $next($request);

		return $response;
	}

}