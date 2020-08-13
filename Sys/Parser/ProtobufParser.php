<?php

namespace Sys\Parser;

use PbRequest;
use PbResponse;
use PbResponseItems;
use \Google\Protobuf\Any;
use \Google\Protobuf\Internal\Message;
use \Sys\Constraint\BaseParser;
use \Sys\Constraint\Object\Request;
use \Sys\Constraint\ParserInterface;
use \Sys\Exceptions\Request\ParseException;

class ProtobufParser extends BaseParser implements ParserInterface {

	const CLASS_PREFIX = 'Pb';

	public function encode($data = ''): string {

		if (app()->isBound('request')) {
			$request = app('request');
			$pbResponse = new PbResponse;
			$pbResponse->setId($request->getId());

			if (!($data instanceof Message)) {
				$pbResponseItems = new PbResponseItems;
				$pbResponseItems->setMsg(is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE));
				$data = $pbResponseItems;
			}
			$any = new \Google\Protobuf\Any();
			$any->pack($data);
			$pbResponse->setData($any);
			return $pbResponse->serializeToString();
		}

		return $data;
	}

	public function decode(string $data = ''): Request{

		$pbRequest = new PbRequest;
		try {
			$pbRequest->mergeFromString($data);

			$id = call_user_func([$pbRequest, 'get' . ucfirst($this->idField)]);
			$uri = call_user_func([$pbRequest, 'get' . ucfirst($this->uriField)]);
			$data = call_user_func([$pbRequest, 'get' . ucfirst($this->dataField)]);

			$request = new Request($uri);
			$request->setId($id);
			$request->setData(
				$data->unpack(),
				Request::DATA_TYPE_ACCESSOR,
				['json_encode' => 'serializeToJsonString']
			);
		} catch (\Throwable $e) {
			throw new ParseException("Data parsing failed");
		}

		return $request;
	}

}