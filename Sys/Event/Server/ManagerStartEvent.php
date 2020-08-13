<?php
namespace Sys\Event\Server;

use Sys\Constraint\EventInterface;
use Sys\Utils\Config;

class ManagerStartEvent implements EventInterface {

	public function handle( ? \Swoole\Server $server = null) {
		swoole_set_process_name('' . Config::get('sys')['process_name'] . ' manager');
	}

}