<?php

namespace App\Exceptions;

use Throwable;
use \Sys\Constraint\ExceptionHandler;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 */
	protected $dontReport = [
	];

	/**
	 * Report or log an exception.
	 */
	public function report(Throwable $exception) {
		parent::report($exception);
	}

	/**
	 * Render an exception into an response.
	 *
	 */
	public function render(Throwable $exception): string {

		return parent::render($exception);

	}
}
