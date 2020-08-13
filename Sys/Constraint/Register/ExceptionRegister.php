<?php

namespace Sys\Constraint\Register;

use ErrorException;
use Throwable;

class ExceptionRegister {

	public function init() {
		error_reporting(E_ALL);
		set_error_handler([$this, 'errorHandler']);
		set_exception_handler([$this, 'exceptionHandler']);
		register_shutdown_function([$this, 'shutdownHandler']);
	}

	public function exceptionHandler(Throwable $e) {
		$handler = $this->getExceptionHandler();
		$handler->report($e);
		$resp = $handler->render($e);
		if (app()->isBound('response')) {
			app('response')->send($resp);
		}

	}

	public function errorHandler($errno, $errstr, $errfile, $errline) {

		$exception = new ErrorException($errstr, 0, $errno, $errfile, $errline);
		if (error_reporting() & $errno) {
			$this->exceptionHandler($exception);
		}
	}

	public function shutdownHandler() {

		if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
			$exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);

			$this->exceptionHandler($exception);
		}
	}

	protected function isFatal(int $type) {
		return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
	}

	protected function getExceptionHandler() {
		return app()->makeOrReplace('user_error_handler', 'error_handler');
	}
}
