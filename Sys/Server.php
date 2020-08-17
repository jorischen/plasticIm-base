<?php
namespace Sys;

use Sys\Constraint\Register\ExceptionRegister;
use Sys\Utils\Config;

class Server {

	protected static $processName;

	protected static $server;

	protected static $workerNum;

	protected static $event = [
		'WorkerStart' => '\Sys\Event\Server\WorkerStartEvent',
		'ManagerStart' => '\Sys\Event\Server\ManagerStartEvent',
		'Receive' => '\Sys\Event\Server\ReceiveEvent',
		'Message' => '\Sys\Event\Server\MessageEvent',
		'Request' => '\Sys\Event\Server\RequestEvent',
	];

	protected static $enableCoroutineEvent = [
		'Receive',
		'Message',
		'Connect',
		'Open',
		'Packet',
		'Request',
		'PipeMessage',
		'Finish',
		'Close',
	];

	public static function start() {

		$serverConfig = Config::get('server');

		static::$server = static::createServer(
			$serverConfig['proto'],
			$serverConfig['host'],
			$serverConfig['port'],
			SWOOLE_PROCESS,
			$serverConfig['ssl'] ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP
		);
		static::setServerConf($serverConfig);
		static::eventRegister();
		static::$server->start();

	}

	/**
	 * Create Server
	 */
	protected static function createServer($proto, $host, $port, $mode, $sockType) {

		switch ($proto) {
		case 'http':
			$serverClass = '\Swoole\Http\Server';
			break;
		case 'websocket':
			$serverClass = '\Swoole\WebSocket\Server';
			break;
		default:
			$serverClass = '\Swoole\Server';
		}

		return new $serverClass(
			$host,
			$port,
			$mode,
			$sockType);

	}

	/**
	 * Server configuration settings
	 */
	protected static function setServerConf($serverConfig) {

		$sysConfig = Config::get('sys');

		static::$processName = $sysConfig['process_name'];

		swoole_set_process_name(static::$processName);

		if (!empty($serverConfig['proto'])) {
			switch ($serverConfig['proto']) {
			case 'http':
				$serverConfig['open_http_protocol'] = true;
				break;
			case 'mqtt':
				$serverConfig['open_mqtt_protocol'] = true;
				break;
			case 'websocket':
				$serverConfig['open_websocket_protocol'] = true;
				break;
			}
		}

		$serverConfig['enable_coroutine'] = false;

		static::$server->set($serverConfig);

		static::$workerNum = $serverConfig['worker_num'] ?? swoole_cpu_num();

		\Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);
	}

	/**
	 * Event Register
	 */
	protected static function eventRegister() {
		array_walk(static::$event, function ($callback, $event) {
			if (in_array($event, static::$enableCoroutineEvent)) {
				$callback = function (...$args) use ($callback) {
					go(function () use ($args, $callback) {
						// set_exception_handler is not supported. You must use try / catch to handle exceptions
						try {
							call_user_func_array([new $callback, 'handle'], $args);
						} catch (\Exception $e) {
							ExceptionRegister::exceptionHandler($e);
						}
					});
				};
			} else {
				$callback = [new $callback, 'handle'];
			}
			static::$server->on($event, $callback);
		});
	}

	/**
	 * Get server
	 */
	public static function getServer() {
		return static::$server;
	}

	/**
	 * Get worker Num
	 */
	public static function getWorkerNum() {
		return static::$workerNum;
	}

}