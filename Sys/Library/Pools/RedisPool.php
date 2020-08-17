<?php

namespace Sys\Library\Pools;

use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool as BaseRedisPool;
use Sys\Constraint\Traits\SingletonTrait;

class RedisPool {

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
			static::$pool = new BaseRedisPool(static::$config, $poolSize);
		}
		return static::$pool;
	}

	public static function setConfig(array $config) {
		if (!isset($config['host'], $config['port'])) {
			throw new \Exception("Redis config error, host and port required");
		}
		$redisConfig = new RedisConfig;
		$redisConfig->withHost($config['host'])
			->withPort($config['port'])
			->withAuth($config['auth'] ?? '')
			->withDbIndex(0)
			->withTimeout($config['timeout'] ?? 3);

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
