<?php

namespace Sys\Parser;

use Sys\Constraint\BaseParser;
use Sys\Constraint\Object\Request;
use Sys\Constraint\ParserInterface;
use Sys\Exceptions\Request\ParseException;

class JsonParser extends BaseParser implements ParserInterface {

	public function encode($data = []): string{

		$response = [$this->dataField => $data];

		if (app()->isBound('request')) {
			$request = app('request');
			$response[$this->idField] = call_user_func([$request, 'get' . ucfirst($this->idField)]);
		}

		return json_encode($response, JSON_UNESCAPED_UNICODE);
	}

	public function decode(string $data = ''): Request{
		$data = json_decode($data);

		if (!$data || !isset($data->{$this->uriField}, $data->{$this->idField}, $data->{$this->dataField})) {
			throw new ParseException("Data parsing failed");
		}

		$request = new Request($data->{$this->uriField});
		$request->setId($data->{$this->idField});
		$request->setData($data->{$this->dataField}, Request::DATA_TYPE_OBJECT);
		return $request;
	}

}