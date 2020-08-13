<?php

use Sys\Container;

if (!function_exists('app')) {
	function app($make = null, array $params = []) {
		if (is_null($make)) {
			return Container::getInstance();
		}
		return Container::getInstance()->make($make, $params);
	}
}