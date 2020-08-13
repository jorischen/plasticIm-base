<?php

namespace Sys\Constraint;

use \Sys\Utils\Config;

abstract class BaseParser {

	protected $uriField;

	protected $dataField;

	protected $idField;

	public function __construct() {
		$config = Config::get('session');
		$this->uriField = $config['uri_field'];
		$this->dataField = $config['data_field'];
		$this->idField = $config['id_field'];
	}

}