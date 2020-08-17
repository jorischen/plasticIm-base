<?php

namespace Sys\Library\Database;

use Sys\Constraint\Traits\SingletonTrait;
use Sys\Library\Pools\RedisPool;

class Redis {

	use SingletonTrait;

	protected $redis;

	protected $multiCmd = 0;

	private function __construct() {
		$redis = app(RedisPool::class)->get();
		\Swoole\Coroutine::defer(function () use ($redis) {
			//Automatic end of command state
			if ($this->multiCmd > 0) {
				$redis->discard();
			}
			app(RedisPool::class)->put($redis);
		});
		$this->redis = $redis;
	}

	public function __call($method, $args) {

		switch ($method) {
		case 'multi':
		case 'watch':
			$this->multiCmd++;
			break;
		case 'exec':
		case 'discard':
			$this->multiCmd = 0;
		case 'unwatch':
			$this->multiCmd--;
			break;
		}

		return call_user_func_array([$this->redis, $method], $args);
	}

}
