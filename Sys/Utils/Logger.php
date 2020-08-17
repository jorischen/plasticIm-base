<?php
namespace Sys\Utils;

use \Sys\Constraint\Traits\SingletonTrait;

class Logger {

	use SingletonTrait;

	protected $levels = [
		'info',
		'notice',
		'warn',
		'error',
	];

	public function write($msg, $type, $fileName = '') {

		$outputMsg = sprintf("%s[%s] %s\n", $fileName, date('H:i:s'), $msg);

		$path = RUNTIME_PATH . '/Log/' . date('Ymd');
		if (!is_dir($path)) {
			mkdir($path, 775, true);
		}
		$file = $path . '/' . $fileName . '.' . $type . '.log';
		file_put_contents($file, $outputMsg, FILE_APPEND);
	}

	public function __call($method, $args) {
		if (in_array($method, $this->levels)) {
			if ($args) {
				$this->write($args[0], $method, ($args[1] ?? ''));
			}
			return;
		}
		throw new \Exception("Call to undefined method {$method}");
	}

}