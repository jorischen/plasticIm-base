<?php

return array(
	'host' => '0.0.0.0',
	'port' => 9501,
	'ssl' => false,
	'proto' => 'websocket', // http | websocket | tcp
	'daemonize' => false, // When daemonize = > 1 is set, the program will run as a daemon
	'log_file' => RUNTIME_PATH . '/Log/server.log',

	// 更多的服务器配置参考文档 Reference configuration
	// [English] https://www.swoole.co.uk/docs/modules/swoole-server/configuration
	// [中文] https://wiki.swoole.com/#/server/setting

	// 自定义协议解析  tcp parser
	'open_eof_check' => false, //EOF detection
	'package_eof' => "\r\n", //EOF symbol
	'open_length_check' => false,
	'package_length_type' => 'v',
	'package_body_offset' => 'N',
	'package_max_length' => 2 * 1024 * 1024, //2M

	// SSL 证书
	'ssl_cert_file' => CONFIG_PATH . '/ssl.crt',
	'ssl_key_file' => CONFIG_PATH . '/ssl.key',

	'buffer_output_size' => 2 * 1024 * 1024, //per process
	'pid_file' => RUNTIME_PATH . '/server.pid',
	'open_tcp_nodelay' => false,

	'send_yield' => true,
);