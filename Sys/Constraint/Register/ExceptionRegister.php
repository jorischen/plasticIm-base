<?php

namespace Sys\Constraint\Register;

use ErrorException;
use Throwable;

class ExceptionRegister {

	public function init() {
		error_reporting(E_ALL);
		set_error_handler([static::class, 'errorHandler']);
		set_exception_handler([static::class, 'exceptionHandler']);
		register_shutdown_function([static::class, 'shutdownHandler']);
	}

	public static function exceptionHandler(Throwable $e) {
		$handler = static::getExceptionHandler();
		$handler->report($e);
		$resp = $handler->render($e);

		if (app()->isBound('response')) {
			app('response')->send($resp);
		}

	}

	public static function errorHandler($errno, $errstr, $errfile, $errline) {
		$exception = new ErrorException($errstr, 0, $errno, $errfile, $errline);
		if (error_reporting() & $errno) {
			//必须抛出异常，否则程序将继续往下执行
			throw $exception;
		}
	}

	public static function shutdownHandler() {
		if (!is_null($error = error_get_last()) && static::isFatal($error['type'])) {
			$exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);

			static::exceptionHandler($exception);
		}
	}

	protected static function isFatal(int $type) {
		return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
	}

	protected static function getExceptionHandler() {
		return app()->makeOrReplace('user_error_handler', 'error_handler');
	}
}
