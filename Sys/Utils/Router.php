<?php
namespace Sys\Utils;

use Sys\Constraint\Traits\SingletonTrait;
use Sys\Exceptions\Route\NotFoundException;

class Router {

	use SingletonTrait;

	/**
	 * Automatic routing
	 * If the routing map does not exist, the controller method is automatically found
	 * @var boolean
	 */
	protected static $automatic = false;

	/**
	 * Routing map
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Controller root namespace
	 * @var array
	 */
	protected static $controllerRootNamespace = '\App\Controller';

	/**
	 * Middleware namespace
	 * @var array
	 */
	protected static $middlewareNamespace = '\App\Middleware';

	/**
	 * Parse URI
	 */
	public static function parse(string $route):  ? Array{

		$success = false;

		if ($routeMap = static::findRouteMap($route)) {

			$namespace = $routeMap['namespace'] ?? '';
			$middleware = $routeMap['middleware'] ?? null;
			$action = $namespace . ($routeMap['method'] ?? $routeMap);
			list($class, $method) = explode('::', $action);
			$success = true;
		} elseif (static::$automatic) {
			$arr = explode('/', $route);
			$arrLen = count($arr);
			if ($arrLen > 1) {
				$method = array_pop($arr);
				$class = implode('\\', $arr);
				$success = true;
			}
		}

		if (!$success) {
			throw new NotFoundException("'{$route}' Route not found");
		}

		$result = static::prepareToCall($namespace ?? '', $class, $method, $middleware ?? null);

		return $result;
	}

	/**
	 * automatic mode
	 * check the routing table first. If it is not found, it will be directly resolved to the controller
	 * @param  bool   $enabled
	 */
	public static function automatic(bool $enabled) {
		static::$automatic = $enabled;
	}

	/**
	 * Add routing map
	 * If there are two parameters, the first is an additional condition, and the second is routing map array;
	 * additional condition fields : middleware, namespace
	 * @param array $args
	 */
	public static function add(...$args) {

		if (count($args) > 1) {
			list($cond, $routes) = $args;
		} else {
			$routes = $args[0];
		}

		$routes = static::toHmap($routes);
		if (!empty($cond)) {
			foreach ($routes as &$v) {
				$method = $v;
				$v = $cond;
				$v['method'] = $method;
			}
		}
		static::$routes = array_merge(static::$routes, $routes);

	}

	/**
	 * route conifg array to hmap
	 */
	protected static function toHmap(array $arr) {
		$hMap = [];
		foreach ($arr as $k => $v) {
			if (is_array($v)) {
				$hm = static::toHmap($v);
				foreach ($hm as $hmk => $hmv) {
					$hMap[$k . '/' . $hmk] = $hmv;
				}
			} else {
				$hMap[$k] = $v;
			}
		}
		return $hMap;
	}

	/**
	 * Find the route from route map
	 */
	public static function findRouteMap(string $route) {
		return static::$routes[$route] ?? null;
	}

	/**
	 * set controller root namespace
	 */
	public static function setControllerRootNamespace(string $namespace) {
		return static::$controllerRootNamespace = '\\' . trim($namespace, '\\ ');
	}

	/**
	 * set controller root namespace
	 */
	public static function setMiddlewareNamespace(string $namespace) {
		return static::$middlewareNamespace = '\\' . trim($namespace, '\\ ');
	}

	/**
	 * Get Controller Class
	 */
	public static function getControllerClass($namespace, $class) {

		$path = [static::$controllerRootNamespace];
		$namespace = trim($namespace, '\\ ');
		if ($namespace) {
			$path[] = $namespace;
		}

		$path[] = $class;

		return implode('\\', $path);
	}

	/**
	 * Check it is callable
	 */
	public static function prepareToCall($namespace, $class, $method, $middleware) {

		$className = static::getControllerClass($namespace, $class);

		if (!is_callable([$className, $method])) {
			throw new NotFoundException("'{$className}::{$method}' Cannot be called");
		}

		if ($middleware) {
			$middleware = static::$middlewareNamespace . '\\' . $middleware;
			if (!is_callable([$middleware, 'handle'])) {
				throw new NotFoundException("'{$middleware}::handle' Cannot be called");
			}
		}

		return ['class' => $className, 'method' => $method, 'middleware' => $middleware];

	}
}