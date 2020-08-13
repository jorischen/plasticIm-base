<?php
namespace Sys\Utils;

use \Sys\Constraint\Traits\SingletonTrait;

class Config {

	use SingletonTrait;

	const DEFAULT_CONFG_PATH = APP_PATH . '/Sys/Config';

	protected static $cache;

	public static function get(string $configFile):  ? Array{

		$keyHash = md5($configFile);
		if (isset(static::$cache[$keyHash])) {
			return static::$cache[$keyHash];
		}

		$fileName = strtolower($configFile) . '.php';
		$fileRootPath = CONFIG_PATH . DIRECTORY_SEPARATOR . $fileName;
		$defFileRootPath = self::DEFAULT_CONFG_PATH . DIRECTORY_SEPARATOR . $fileName;

		$config = [];
		if (file_exists($fileRootPath)) {
			$config = require $fileRootPath;
		}

		if (file_exists($defFileRootPath)) {
			$config = array_merge(require $defFileRootPath, $config);
		}

		static::$cache[$keyHash] = $config;
		return $config;
	}
}