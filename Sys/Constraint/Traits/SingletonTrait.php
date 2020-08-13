<?php

namespace Sys\Constraint\Traits;

trait SingletonTrait {

	protected static $instance;

	/**
	 * Get singleton Instance
	 * Compatible with coroutine and non coroutine
	 */
	public static function getInstance(...$args) {

		$context = \Swoole\Coroutine::getContext();

		$calledClass = get_called_class();

		if (!is_null($context)) {
			// coroutine state
			if (!isset($context[$calledClass])) {

				//If from non coroutine state to coroutine state,then extend static::$instance
				if (static::$instance) {
					$context[$calledClass] = clone static::$instance;
				} else {
					$context[$calledClass] = new static(...$args);
				}
			}
		} else {
			//non coroutine state
			$context[$calledClass] = &static::$instance;
			$context[$calledClass] or $context[$calledClass] = new static(...$args);
		}

		return $context[$calledClass];
	}

	private function __construct() {}

	public function __clone() {}
}