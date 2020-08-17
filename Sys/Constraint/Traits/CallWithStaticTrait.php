<?php

namespace Sys\Constraint\Traits;

trait CallWithStaticTrait {

	public function __call($method, $args) {
		return call_user_func_array([static::class, $method], $args);
	}
}