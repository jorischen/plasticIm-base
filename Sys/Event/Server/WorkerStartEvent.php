<?php
namespace Sys\Event\Server;

use Sys\App;
use Sys\Constraint\EventInterface;
use Sys\Utils\Config;

class WorkerStartEvent implements EventInterface {

	public function handle( ? \Swoole\Server $server = null, int $workerId = 0) {
		swoole_set_process_name('' . Config::get('sys')['process_name'] . ' worker');
		$app = new App;
		$app->run();
	}

}