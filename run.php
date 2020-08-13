<?php

// If you want to redirect the worker process directory (security enhancement), change it here instead of the server's config
//chroot(realpath(__DIR__));
define('DEBUG', true);
define('APP_PATH', realpath(getcwd()));
define('CONFIG_PATH', APP_PATH . '/Config');
define('RUNTIME_PATH', APP_PATH . '/Runtime');

require APP_PATH . '/Sys/bootstrap.php';

Sys\Server::start();