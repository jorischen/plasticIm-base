<?php
namespace Sys;

use Sys\Constraint\Register\ExceptionRegister;
use Sys\Library\Pools\PDOPool;
use Sys\Library\Pools\RedisPool;
use Sys\Utils\Config;

class App {

	/**
	 * Provider Register
	 */
	protected $providerRegister = [
		\App\Provider\ServiceRegister::class,
	];

	/**
	 * Run app
	 */
	public function run() {
		$this->init();
	}

	/**
	 * App initialization
	 */
	public function init() {
		$this->settingsInit();
		$this->register();
		$this->loadRoute();
		$this->excessInit();
		$this->customInit();
	}

	/**
	 * Settings initialization
	 */
	protected function settingsInit() {

		$config = Config::get('sys');
		date_default_timezone_set($config['timezone'] ?? 'UTC');

		(new ExceptionRegister)->init();
	}

	/**
	 * Load route
	 */
	protected function loadRoute() {
		require APP_PATH . '/App/Route/app.php';
	}

	/**
	 * custom initialization
	 */
	protected function customInit() {
		if (is_callable(['\App\App', 'init'])) {
			(new \App\App)->init();
		}
	}

	/**
	 * Provider Register
	 */
	protected function register() {

		foreach ($this->providerRegister as $v) {
			if (is_callable($v)) {
				$providerRegister = new $v;
				$providerRegister->register();
			}
		}

		$bootstrap = Config::get('bootstrap');

		if (!empty($bootstrap['container']['register'])) {
			foreach ($bootstrap['container']['register'] as $k => $v) {
				if (is_string($v)) {
					app()->bind($k, $v);
				} else if (is_array($v)) {
					if (!empty($v['is_singleton'])) {
						app()->bindSingleton($k, $v['bind']);
					}
				}
			}
		}

		if (!empty($bootstrap['container']['singleton_trait'])) {
			app()->setSingletonTrait(
				$bootstrap['container']['singleton_trait']['trait'],
				$bootstrap['container']['singleton_trait']['get_instance_method'],
			);
		}

		//pool
		$database = Config::get('database');
		if ($database) {
			if (!empty($database['redis'])) {
				app()->bind(RedisPool::class, RedisPool::class);
				app(RedisPool::class)->setConfig($database['redis']);
			}

			if (!empty($database['database'])) {
				app()->bind(PDOPool::class, PDOPool::class);
				app(PDOPool::class)->setConfig($database['database']);
			}
		}
	}

	/**
	 * excess initialization
	 */
	protected function excessInit() {
		$config = Config::get('session');

		//Map from message names to sub-maps
		if ($config['parser'] === \Sys\Parser\ProtobufParser::class) {
			$fileSuffix = '.php';
			$GPBMetadata = glob(APP_PATH . '/' . $config['protobuf_path'] . '/GPBMetadata/*' . $fileSuffix);
			if ($GPBMetadata) {
				foreach ($GPBMetadata as $filename) {
					$name = preg_replace('/.+\/(.+?)(\..+?)$/', '$1', $filename);
					$className = '\GPBMetadata\\' . $name;
					$call = [$className, 'initOnce'];
					call_user_func($call);
				}
			}

		}
	}

}