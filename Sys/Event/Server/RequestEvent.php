<?php
namespace Sys\Event\Server;

use Sys\Constraint\Event\EventInterface;
use Sys\Constraint\Object\Request;
use Sys\Constraint\Object\Response;

/**
 * HTTP SERVER Receive Event
 */
class RequestEvent implements EventInterface {

	protected $dataParser;

	public function handle( ? \Swoole\Http\Request $httpRequest = null,  ? \Swoole\Http\Response $httpResponse = null) {

		//bind response
		$parser = $this->getParser();
		$response = new Response;
		$response->bindSender(function ($data) use ($httpResponse, $parser) {
			$httpResponse->end($parser->encode($data));
		});
		app()->bind('response', $response);

		//resolve request
		$this->resolve($httpRequest);

	}

	protected function resolve($httpRequest) {

		$data = $httpRequest->rawContent() ?: '';

		$request = $this->parseData($data);

		//Bind to container as a dependency
		app()->bindDependent(Request::class, $request)->bind('request', $request);

		$uri = $request->getUri();

		$caller = app('route')->parse($uri);

		if ($caller['middleware']) {
			$next = function ($request) use ($caller) {
				return app()->call($caller['class'], $caller['method']);
			};
			$response = app()->call(
				$caller['middleware'],
				'handle',
				['request' => $request, 'next' => $next]
			);
		} else {
			$response = app()->call($caller['class'], $caller['method']);
		}

		app('response')->send($response);
	}

	protected function parseData($data): Request {
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