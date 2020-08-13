<?php

namespace App\Controller;

use Sys\Constraint\Object\Request;

/**
 *
 */
class DemoController {

	public function hello(Request $request) {

		$params = $request->all();

		$name = $request->nickname;

		return ['data' => "hello {$request->nickname}", 'msg' => 'success', 'code' => 200];
	}
}