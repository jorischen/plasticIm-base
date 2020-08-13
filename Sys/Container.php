<?php
namespace Sys;

use Sys\Exceptions\Container\CallException;
use \Sys\Constraint\Traits\SingletonTrait;
use \Sys\Exceptions\Container\BindException;
use \Sys\Exceptions\Container\MakeException;

class Container {

	use SingletonTrait;

	protected $bindings = [];

	protected $dependencies = [];

	/**
	 * Bind to container
	 * @param  string       $name
	 * @param  mixed        $value
	 * @param  bool|boolean $cover
	 */
	public function bind(string $name, $value, bool $cover = false) {
		if (isset($this->bindings[$name]) && !$cover) {
			throw new BindException("Bind Error, The name '$name' already exists");
		}

		if ($cover) {
			$this->unbind($name);
		}
		$this->bindings[$name]['bind'] = $value;
		return $this;
	}

	/**
	 * Singleton bind to container
	 * @param  string       $name
	 * @param  string       $class
	 * @param  string        $getInstanceMethod
	 * @param  bool|boolean $cover
	 */
	public function bindSingleton(string $name, string $class, string $getInstanceMethod = '', bool $cover = false) {

		if ($getInstanceMethod) {

			if (!is_callable([$class, $getInstanceMethod])) {
				throw new BindException("Singleton bind Error, This method '{$class}::{$getInstanceMethod}' cannot be called");
			}

			$reflectionMethod = new \ReflectionMethod($class, $getInstanceMethod);
			if (!$reflectionMethod->isStatic()) {
				throw new BindException("Singleton bind Error, This method '{$class}::{$getInstanceMethod}' should be statically");
			}

		} else if (!class_exists($class)) {

			throw new BindException("Singleton bind Error, This class '{$class}' does not exist");
		}

		$this->bind($name, function () use ($class, $getInstanceMethod) {
			if ($getInstanceMethod) {
				return call_user_func([$class, $getInstanceMethod]);
			}

			return $this->make($class);

		}, $cover);

		$this->bindings[$name]['is_singleton'] = true;
		return $this;
	}

	/**
	 * Bind Instance
	 * @param  string  $name
	 * @param  mixed   $instance
	 * @param  bool|boolean $cover
	 */
	public function bindInstance(string $name, $instance, bool $cover = false) {

		if (isset($this->bindings[$name]['instance']) && !$cover) {
			throw new BindException("Instance bind Error, The name '$name' already exists");
		}
		$this->bindings[$name]['instance'] = $instance;
		return $this;
	}

	/**
	 * Resolve the given type from the container.
	 * @param  string $name
	 * @param  array  $params
	 */
	public function make(string $name, array $params = []) {

		$abstract = $name;

		//If bound, returns the bound value or instance
		if (isset($this->bindings[$name])) {

			if (isset($this->bindings[$name]['instance'])) {
				return $this->bindings[$name]['instance'];
			}

			$abstract = $this->bindings[$name]['bind'];
		}

		if (is_string($abstract) && class_exists($abstract)) {

			$reflectionClass = new \ReflectionClass($abstract);

			$constructor = $reflectionClass->getConstructor();
			$isInstantiable = $reflectionClass->isInstantiable();

			//If a constructor exists and cannot be instantiated, it is considered a static class
			if ($constructor) {

				if ($isInstantiable) {

					$constructorParams = $constructor->getParameters();
					$dependencies = $this->makeDependencies($constructorParams, $params);

				} else {
					return $this->makeStaticClassInstance($abstract, $name);
				}
			}

			if (!$isInstantiable) {
				throw new MakeException("Class '{$name}' cannot be instantiated");
			}

			return $reflectionClass->newInstanceArgs($dependencies ?? []);
		}

		//If it is callable, return call result
		if (is_callable($abstract)) {

			$result = call_user_func_array($abstract, $params);

			//If it is declared as a singleton, the instance is bound to the container
			if ($this->isSingleton($name)) {
				$this->bindInstance($name, $result, true);
			}
			return $result;
		}

		//If it is object, bind to instance
		if (is_object($abstract)) {
			$this->bindInstance($name, $abstract);
		}

		//Other binding values
		if ($abstract !== $name) {
			return $abstract;
		}

		throw new MakeException("Unable to make target, name '{$name}'");
	}

	/**
	 * Find Dependencies
	 * @param  string $name
	 */
	public function findDependencies(string $name) {
		return $this->dependencies[$name] ?? null;
	}

	/**
	 * Bind Dependent
	 * @param  string $name
	 */
	public function bindDependent(string $name, $value) {
		$this->dependencies[$name] = $value;
		return $this;
	}

	/**
	 * Unbind Dependent
	 * @param  string $name
	 */
	public function unbindDependent(string $name) {
		unset($this->dependencies[$name]);
		return $this;
	}

	/**
	 * Is Singleton
	 * @param  string $name
	 */
	protected function isSingleton(string $name) {
		return isset($this->bindings[$name]['is_singleton']);
	}

	/**
	 * Make Static Class Instance
	 * @param  string $staticClass
	 * @param  string $alias
	 */
	protected function makeStaticClassInstance(string $staticClass, string $alias = '') {

		$instance = new class($staticClass) {

			private $callClass;

			public function __construct($callClass) {
				$this->callClass = $callClass;
			}

			public function getClass() {
				return $this->callClass;
			}

			public function __call($name, $args) {
				return call_user_func_array([$this->callClass, $name], $args);
			}
		};

		$this->bindInstance($alias ?: $staticClass, $instance, true);

		return $instance;
	}

	/**
	 * Make Dependencies
	 * @param  array  $params
	 * @param  array $values
	 */
	protected function makeDependencies(array $params, array $values): array{

		$dependencies = [];

		foreach ($params as $p) {

			$pClass = $p->getClass();

			if ($pClass) {

				if ($values) {
					if (isset($values[$p->name]) && ($values[$p->name] instanceof $pClass->name)) {
						$dependencies[] = $values[$p->name];
						continue;
					} else if (isset($values[$pClass->name])) {
						$dependencies[] = $values[$pClass->name];
						continue;
					}
				}

				$dependencies[] = $this->findDependencies($pClass->name);

			} else if (array_key_exists($p->name, $values)) {
				$dependencies[] = $values[$p->name];
			} else {
				throw new MakeException("The declared dependency '{$p}' cannot be found");
			}
		}

		return $dependencies;
	}

	/**
	 * If the first one make fails, then try the second one and override the first one binding
	 * @param  string $name
	 * @param  string $replace
	 */
	public function makeOrReplace(string $name, string $replace) {

		if (isset($this->bindings[$name]['instance'])) {
			return $this->bindings[$name]['instance'];
		};

		$instance = $this->make($name);
		if (!isset($this->bindings[$name]['instance'])) {
			$instance = $this->make($replace);
			$this->bindInstance($name, $instance, true);
		}

		return $instance;
	}

	/**
	 * Unbind
	 * @param  string $name
	 */
	public function unbind(string $name) {
		unset($this->bindings[$name]);
	}

	/**
	 * is bound
	 * @param  string $name
	 */
	public function isBound(string $name) {
		return array_key_exists($name, $this->bindings);
	}

	/**
	 * call
	 * @param  string $method
	 */
	public function call($class, string $method, array $params = []) {

		if (!is_callable([$class, $method])) {
			$class = strval($class);
			throw new CallException("Method '{$class}::{$method}' cannot be called");
		}

		$reflectionMethod = new \ReflectionMethod($class, $method);
		$reflectionMethodParams = $reflectionMethod->getParameters();
		$dependencies = $this->makeDependencies($reflectionMethodParams, $params);
		$product = $this->make($class);
		return $reflectionMethod->invokeArgs($product, $dependencies);
	}

	/**
	 * Get resources in the form of attributes
	 * @param  string $name
	 */
	public function __get(string $name) {
		return $this->make($name);
	}

}