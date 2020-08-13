<?php
namespace Sys\Event\Server;

use Sys\Constraint\EventInterface;
use Sys\Constraint\Object\Request;
use Sys\Register\ExceptionRegister;

/**
 * TCP SERVER Receive Event
 */
class ReceiveEvent implements EventInterface {

	protected $dataParser;

	public function handle( ? \Swoole\Server $server = null, int $fd = 0, int $reactorId = 0, string $data = '') {

		$request = $this->parseData($data);

		//Bind to container as a dependency
		app()->bindDependent(Request::class, $request)->bind('request', $request);

		$uri = $request->getUri();

		try {
			$caller = app('router')->parse($uri);
		} catch (\Exception $e) {
			return app(ExceptionRegister::class)->exceptionHandler($e);
		}

		if ($caller['middleware']) {
			$next = function ($request) use ($caller) {
				app()->call($caller['class'], $caller['method']);
			};
			$response = app()->call($caller['middleware'], 'handle', ['request' => $request]);
		} else {
			$response = app()->call($caller['class'], $caller['method']);
		}

		$server->send($fd, $this->encodeData($response));

	}

	protected function parseData($data) : Request {
		return $this->getParser()->decode($data);
	}

	protected function encodeData($data) {
		return $this->getParser()->encode($data);
	}

	protected function getParser() {

		if (!$this->dataParser) {
			$sessConfig = app('config')->get('session');
			$this->dataParser = new $sessConfig['parser'];
		}
		return $this->dataParser;
	}

}