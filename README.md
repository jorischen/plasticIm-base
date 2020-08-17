# plasticIm base 高性能的PHP通讯服务框架

[中文](./README.md) | [English](./README.en.md)

### 介绍
+ 基于Swoole协程实现，可以同时支持数百万TCP连接在线
+ 支持tcp，websocket，http服务; 通过配置轻松切换，无需修改代码
+ 内置通讯数据格式：Protocol Buffers, Json;  通过配置轻松切换，无需修改代码
+ 支持扩展任意通讯格式
+ 支持redis、mysql连接池
+ 支持自适应协程和非协程状态的IOC容器，依赖注入

框架的基本目录结构以及使用方式都和传统的基于PHP-FPM的主流框架一样，轻松使用，零学习成本

### 目录结构
```
App            业务代码
	Controller       控制器
	Exceptions       异常处理
	Middleware       路由中间件
	Provider         服务注册绑定
	Route            路由
	App.php          服务初始化

Config          配置项
	
Protobuf        protobuf 协议及编译文件
	Dist              编译文件输出目录
	build.sh          批量编译脚本
	*.proto           protobuf 源文件

Runtime         运行时目录 (需要写入权限)
	Log                默认日志目录

Sys             框架核心代码

Test            测试代码
```


### 依赖

###### PHP7.3+

###### 需要Swoole-4.4.1或更高版本  
详细的安装指引  https://github.com/swoole/swoole-src#%EF%B8%8F-installation

```
pecl install swoole
```


###### 如果你需要使用Protocol Buffers (比json、XML更小、更快)

编译器安装  https://github.com/protocolbuffers/protobuf/releases

PHP包安装   
+ PHP扩展   https://github.com/protocolbuffers/protobuf/tree/master/php#c-extension  
或者   
+ composer包  https://github.com/protocolbuffers/protobuf/tree/master/php#php-package   
 `composer require google/protobuf `

### 使用说明

##### 初始化composer  
	
	`composer install`

##### 启动服务器
	
	`php run.php`

##### 测试

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