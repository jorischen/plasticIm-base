<?php
namespace Sys\Library\Pools;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool as BasePDOPool;
use Sys\Constraint\Traits\SingletonTrait;

class PDOPool {

	use SingletonTrait;

	protected static $pool;

	protected static $config;

	/**
	 * Number of connections
	 * @var integer
	 */
	protected static $poolSize = 200;

	public static function getPool() {
		if (!static::$pool) {
			$poolSize = floor(static::$poolSize / app('server')->getWorkerNum());
			static::$pool = new BasePDOPool(static::$config, $poolSize);
		}
		return static::$pool;
	}

	public static function setConfig(array $config) {
		if (!isset($config['host'], $config['port'], $config['db_name'], $config['username'], $config['password'])) {
			throw new \Exception("PDO config error, host and port required");
		}
		$redisConfig = new PDOConfig;
		$redisConfig->withHost($config['host'])
			->withPort($config['port'])
			->withDbName($config['db_name'])
			->withCharset($config['charset'] ?? 'utf8mb4')
			->withPassword($config['password'])
			->withUsername($config['username']);

		if (!empty($config['pool_size'])) {
			static::$poolSize = $config['pool_size'];
		}
		static::$config = $redisConfig;
	}

	public function __call($method, $args) {
		return call_user_func_array([static::getPool(), $method], $args);
	}

	public static function __callStatic($method, $args) {
		return call_user_func_array([static::getPool(), $method], $args);
	}

}
