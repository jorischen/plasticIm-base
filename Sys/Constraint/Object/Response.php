<?php

namespace Sys\Constraint\Object;

class Response {

	protected $sender;

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

	public function __construct() {

	}

	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param string $sender
	 */
	public function bindSender(\Closure $sender) {
		$this->sender = $sender;
		return $this;
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
	public function setData($data, string $type, array $replacement = []) {

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
	 * send
	 */
	public function send($data = '') {
		return $this->getSender()($data);
	}

}