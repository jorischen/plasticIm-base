<?php

namespace App\Provider;

use \Sys\Constraint\ServiceProvider;

class ServiceRegister extends ServiceProvider {
	/**
	 * Register any application services to container.
	 *
	 * Simple binding :
	 * 		example: app()->bind('my_service', MyService::class);
	 *
	 * Bind Instance (If the instance creation is complicated):
	 * 		example: app()->bindInstance('my_service', new MyService($specialDependence));
	 *
	 * Bind Singleton :
	 * 		example: app()->bindSingleton('my_service', MyService::class, 'getInstance');
	 *
	 * Usage :
	 * 		app('my_service')
	 *
	 */
	public function register() {

	}
}
