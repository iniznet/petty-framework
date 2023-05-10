<?php

namespace Petty\Routing;

class Route
{
	use Concerns\Responses;

	private static array $routes = [];
	private static string|null $currentUri = null;
	private static string|null $controller = null;
	private static array $middlewares = [];
	
	public static array $verbs = [
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH',
		'OPTIONS',
		'ANY'
	];

	/**
	 * Chainable static route class
	 * 
	 * 1. add() method to add a specific route method to the routes array
	 * 2. get() method to add a GET route to the routes array
	 * 3. post() method to add a POST route to the routes array
	 * 4. put() method to add a PUT route to the routes array
	 * 5. delete() method to add a DELETE route to the routes array
	 * 6. patch() method to add a PATCH route to the routes array
	 * 7. options() method to add a OPTIONS route to the routes array
	 * 8. any() method to add a ANY route to the routes array
	 * 9. group() method to add a group of routes to the routes array
	 * 10. middleware() method to add a middleware to the chained route method
	 * 11. run() method to run the routes array
	 * 
	 */

	
	public static function add(string|array $methods, string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		$methods = is_array($methods) ? $methods : [$methods];

		if (is_string($action) && strpos($action, '@') !== false) {
			$segments = explode('@', $action);
			$action = [
				'controller' => $segments[0],
				'method' => $segments[1]
			];
		}

		if (count(self::$middlewares) > 0) {
			$middlewares = array_merge($middlewares, self::$middlewares[self::$currentUri]);
		}

		if (self::$controller !== null) {
			$action = [
				'controller' => self::$controller,
				'method' => $action
			];
		}

		foreach ($methods as $method) {
			if (!in_array($method, self::$verbs)) {
				throw new \Exception("Invalid route method: {$method}");
			}

			self::$routes[$method][$uri] = [
				'action' => $action,
				'middlewares' => $middlewares
			];

			self::$currentUri = $uri;
		}

		self::$currentUri = null;
		self::$controller = null;
		self::$middlewares = [];

		return new self;
	}

	public static function get(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('GET', $uri, $action, $middlewares);
	}

	public static function post(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('POST', $uri, $action, $middlewares);
	}
	
	public static function put(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('PUT', $uri, $action, $middlewares);
	}

	public static function delete(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('DELETE', $uri, $action, $middlewares);
	}

	public static function patch(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('PATCH', $uri, $action, $middlewares);
	}

	public static function options(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('OPTIONS', $uri, $action, $middlewares);
	}

	public static function any(string $uri, array|string|callable|null $action, array $middlewares = []): self
	{
		return self::add('ANY', $uri, $action, $middlewares);
	}

	public static function controller(array|string|null $controller, array $middlewares = []): self
	{
		if (is_string($controller) && strpos($controller, '@') !== false) {
			$segments = explode('@', $controller);
			$controller = [
				'controller' => $segments[0],
				'method' => $segments[1]
			];
		}

		self::$controller = $controller;
		self::$middlewares[self::$currentUri] = $middlewares;

		return new self;
	}

	public static function group(callable $callback): self
	{
		$callback();

		return new self;
	}

	public static function middleware(string|array $middlewares): self
	{
		if (self::$currentUri === '') {
			throw new \Exception('Cannot add middleware to a unspecified route');
		}

		self::$middlewares[self::$currentUri] = is_array($middlewares) ? $middlewares : [$middlewares];

		return new self;
	}

	public static function run(): void
	{
		$uri = $_SERVER['REQUEST_URI'];
		$uri = parse_url($uri, PHP_URL_PATH);
		$uri = explode('?', $uri)[0];
		$uri = trim($uri, '/');
		$method = $_SERVER['REQUEST_METHOD'];

		if (!array_key_exists($method, self::$routes)) {
			self::response([
				'status' => 405,
				'message' => 'Method not allowed'
			], 405);
		}

		if (!array_key_exists($uri, self::$routes[$method])) {
			self::response([
				'status' => 404,
				'message' => 'Not found'
			], 404);
		}

		$action = self::$routes[$method][$uri]['action'];
		$middlewares = self::$routes[$method][$uri]['middlewares'];

		if (count($middlewares) > 0) {
			foreach ($middlewares as $middleware) {
				$middleware = new $middleware;
				$middleware->handle(function () use ($action) {
					self::handle($action);
				});
			}
		} else {
			self::handle($action);
		}

		self::response([
			'status' => 404,
			'message' => 'Not found'
		], 404);

		exit;
	}

	private static function handle(array $action): void
	{
		if (is_callable($action)) {
			$action();
			exit;
		}

		if (is_array($action)) {
			$controller = new $action['controller'];
			$method = $action['method'];
			$controller->$method();
			exit;
		}

		if (is_string($action)) {
			$segments = explode('@', $action);
			$controller = new $segments[0];
			$method = $segments[1];
			$controller->$method();
			exit;
		}
	}
}
