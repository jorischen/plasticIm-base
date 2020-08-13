<?php

namespace Sys\Constraint\Object;

use \Sys\Constraint\RequestInterface;

class Request implements RequestInterface {

	protected $id;

	protected $uri;

	protected $data;

	protected $dataType;

	/**
	 * Event replacement
	 * base function=>replace to function
	 */
	protected $replacement = [
		'json_encode' => null,
	];

	const DATA_TYPE = ['object', 'array', 'accessor'];

	const DATA_TYPE_OBJECT = 'object';

	const DATA_TYPE_ARRAY = 'array';

	const DATA_TYPE_ACCESSOR = 'accessor';

	public function __construct(string $uri) {
		$this->setUri($uri);
	}

	public function getUri() {
		return $this->uri;
	}

	/**
	 * @param string $uri
	 */
	public function setUri(string $uri) {
		$this->uri = $uri;
		return $this;
	}

	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId(string $id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * set request data
	 * @param mixed $data
	 * @param string  $type
	 * @param array  $replacement
	 */
	public function setData($data = '', string $type = '', array $replacement = []) {

		if (!in_array($type, static::DATA_TYPE)) {
			throw new \Exception("Only these types are supported : " . implode(',', static::DATA_TYPE));
		}
		$this->data = $data;
		$this->dataType = $type;

		if ($replacement) {
			$this->replacement = array_merge($this->replacement, $replacement);
		}

		return $this;
	}

	/**
	 * Get all data fields as an array
	 */
	public function all(): array{

		if (static::DATA_TYPE_ARRAY === $this->dataType) {
			return $this->data;
		}
		return json_decode($this->toJson(), true);
	}

	/**
	 * Serialize to JSON
	 */
	public function toJson(): string {

		if (static::DATA_TYPE_ACCESSOR === $this->dataType && !empty($this->replacement['json_encode'])) {
			$callFunc = [$this->data, $this->replacement['json_encode']];
			if (is_callable($callFunc)) {
				return call_user_func($callFunc);
			}
		}
		return json_encode($this->data);
	}

	/**
	 * Read data by property
	 */
	public function __get($name) {

		switch ($this->dataType) {
		case static::DATA_TYPE_OBJECT:
			if (isset($this->data->{$name})) {
				return $this->data->{$name};
			}
			break;
		case static::DATA_TYPE_ARRAY:
			if (isset($this->data[$name])) {
				return $this->data[$name];
			}
			break;
		case static::DATA_TYPE_ACCESSOR:
			$callFunc = [$this->data, 'get' . ucfirst($name)];
			if (is_callable($callFunc)) {
				return call_user_func($callFunc);
			}
			break;
		}
		return null;
	}
}