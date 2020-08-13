<?php

namespace Sys\Constraint;

class ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 */
	protected $dontReport = [
	];

	/**
	 * Report or log an exception.
	 */
	public function report(\Throwable $exception) {

		app('log')->error($exception);
	}

	/**
	 * Render an exception into an response.
	 *
	 */
	public function render(\Throwable $exception): string {

		return DEBUG ? $exception : $exception->getMessage();

	}

}