<?php
namespace Test;

error_reporting(E_ALL);
define('DEBUG', 'on');
define('APP_PATH', realpath(__DIR__ . '/../'));
define('CONFIG_PATH', APP_PATH . '/Config');
define('RUNTIME_PATH', APP_PATH . '/Runtime');

require APP_PATH . '/Sys/bootstrap.php';

go(function () {

	$client = new Client('127.0.0.1', 9501);

	//#json
	// $r = $client->post('/hello', json_encode(['id' => 123, 'uri' => 'user/info', 'data' => ['nickname' => 'John']]));

	//#protobuf-------HTTP
	//$client->post('/hello', getProtoData());
	//print_r($client);

	//#protobuf-------WESOCKET
	$client->upgrade()->send(getProtoData());

});

class Client {

	protected $client;

	function __construct($host, $port) {
		$client = new \Swoole\Coroutine\Http\Client($host, $port);
		$client->setHeaders([
			'Content-Type' => 'application/json;charset=utf-8',
			'token' => md5('user token'),
		]);

		$this->client = $client;
	}

	public function upgrade() {
		$ret = $this->client->upgrade("/");
		if (!$ret) {
			throw new \Exception("connect failed");
		}
		return $this;
	}

	public function send($data) {
		$this->client->push($data);
		echo "--------- send --------------: " . PHP_EOL . $data . PHP_EOL;
		while (true) {
			if ($resp = $this->client->recv()) {
				echo "--------- response ----------: " . PHP_EOL;
				print_r($this->decode($resp->data));
			}
			\co::sleep(1);
		}
	}

	public function decode($data) {

		$b = new \PbResponse;
		$b->mergeFromString($data);
		$items = $b->getData()->unpack();
		$r['data'] = [
			'msg' => $items->getMsg(),
			'code' => $items->getCode(),
			'data' => $items->getData(),
		];
		$r['id'] = $b->getId();
		return $r;
	}

	function __call($method, $args) {
		return call_user_func_array([$this->client, $method], $args);
	}

}

function getProtoData() {
	$a = new \PbRequest;

	$a->setId(uniqid());
	$a->setUri('hello');

	$data = new \PbUser();
	$data->setNickname('Yao Ming');
	$data->setUid('10000');

	$any = new \Google\Protobuf\Any();
	$any->pack($data);
	$a->setData($any);
	$output = $a->serializeToString();
	return $output;
}
