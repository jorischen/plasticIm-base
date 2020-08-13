<?php

return array(
	'parser' => \Sys\Parser\JsonParser::class, // JsonParser | ProtobufParser | any custom parser
	'uri_field' => 'uri', // uri field
	'data_field' => 'data', // data field
	'id_field' => 'id', // id field

	//protobuf
	'protobuf_path' => 'Protobuf/Dist',
);
