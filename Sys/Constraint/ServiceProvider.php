<?php

namespace Sys\Constraint;

abstract class ServiceProvider {

	/**
	 * Register any application services.
	 * example: app()->bind('my_service', \App\Service\MyService::class)
	 */
	abstract public function register();

}
