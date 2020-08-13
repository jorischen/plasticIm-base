# plasticIm base （High performance PHP communication service framework）

[中文](./README.md) | [English](./README.en.md)

### Describe
+ swoole coroutine-based，It can support millions of TCP connections online at the same time
+ Support TCP, websocket, HTTP services; switch easily through configuration without modifying code
+ Built in communication data format: Protocol buffers, JSON; easy switching through configuration, without code modification
+ Support the extension of any communication format
+ IOC container supporting adaptive corouting and non coprocessing States, dependency injection

The basic directory structure and usage of the framework are the same as the traditional mainstream framework based on php-fpm, easy to use and zero learning cost

### Directory 
```
App            Your code
	Controller       request controller
	Exceptions       exception handling
	Middleware       Routing Middleware
	Provider         Service registration
	Route            route
	App.php          Service initialization

Config          Configuration
	
Protobuf        protobuf protocol and compiled files
	Dist              Compiled file output directory
	build.sh          Batch compiling scripts
	*.proto           protobuf source file

Runtime         Runtime directory (write permission required)
	Log                Default log directory

Sys             Framework core code

Test            Test code
```


### Dependency 

###### PHP7.3+

###### Swoole-4.4.1 or higher is required
installation guide  https://github.com/swoole/swoole-src#%EF%B8%8F-installation

```
pecl install swoole
```


###### If you need to use protocol buffers (smaller and faster than JSON and XML)

Compiler installation  https://github.com/protocolbuffers/protobuf/releases

PHP package installation   
+ PHP extension   https://github.com/protocolbuffers/protobuf/tree/master/php#c-extension  
or   
+ composer package  https://github.com/protocolbuffers/protobuf/tree/master/php#php-package   
 `composer require google/protobuf `

### Usage

##### Initialize composer   
	
	`composer install`

##### Start the server
	
	`php run.php`

##### Test

	`php ./Test/test.php`

```
--------- send --------------: 

5f34b02b08106hello-
type.googleapis.com/User                                                                                                                                                                    Yao Ming10000
--------- response ----------: 
Array
(
    [data] => Array
        (
            [msg] => {"data":"hello Yao Ming","msg":"success","code":200}
            [code] => 0
            [data] => 
        )

    [id] => 5f34b02b08106
)
```