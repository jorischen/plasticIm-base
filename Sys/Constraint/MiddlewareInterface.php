<?php

namespace Sys\Constraint;

use Closure;

interface MiddlewareInterface {

	public function handle($request, Closure $next);

}