<?php

namespace Sys\Constraint;

use Sys\Constraint\Object\Request;

interface ParserInterface {

	public function encode(): string;

	public function decode(): Request;

}