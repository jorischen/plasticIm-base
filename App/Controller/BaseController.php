<?php

namespace App\Controller;

class BaseController {

	public function response(array $data = [], int $code = 200, string $msg = '') {
		$resp = [
			'data' => $data,
			'msg' => $msg,
			'code' => $code,
		];
		return $resp;
	}
}