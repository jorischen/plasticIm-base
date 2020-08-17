<?php

namespace Sys\Library\Database;

use Sys\Constraint\Traits\SingletonTrait;
use Sys\Library\Pools\PDOPool;

class Mysql {

	use SingletonTrait;

	protected $mysql;

	protected $beginTransaction = false;

	private function __construct() {
		$mysql = app(PDOPool::class)->get();
		\Swoole\Coroutine::defer(function () use ($mysql) {
			//Automatic end of command state
			if ($this->beginTransaction) {
				$mysql->rollBack();
			}
			app(PDOPool::class)->put($mysql);
		});
		$this->mysql = $mysql;
	}

	public function __call($method, $args) {

		switch ($method) {
		case 'beginTransaction':
			$this->beginTransaction = true;
			break;
		case 'rollBack':
		case 'commit':
			$this->beginTransaction = false;
		}

		return call_user_func_array([$this->mysql, $method], $args);
	}

}
