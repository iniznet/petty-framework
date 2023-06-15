<?php

namespace Petty\Routing;

class Route
{
	use Concerns\Responses;

	private static array $routes = [];
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
		$uri = trim($uri, '/');
		$uri = $uri ?: '/';

		if (is_string($action) && strpos($action, '@') !== false) {
			$segments = explode('@', $action);
			$action = [
				'controller' => $segments[0],
				'method' => $segments[1]
			];
		}

		if (is_array($action)) {
			$action = [
				'controller' => $action[0],
				'method' => $action[1]
			];
		}

		if (self::$middlewares) {
			$middlewares = array_merge($middlewares, self::$middlewares);
		}

		foreach ($methods as $method) {
			if (!in_array($method, self::$verbs)) {
				throw new \Exception("Invalid route method: {$method}");
			}

			self::$routes[$method][$uri] = [
				'action' => $action,
				'middlewares' => $middlewares,
				'uri' => $uri,
			];
		}

		return new self;
	}

	public static function get(string $uri, array|string|callable|null $action): self
	{
		return self::add('GET', $uri, $action);
	}

	public static function post(string $uri, array|string|callable|null $action): self
	{
		return self::add('POST', $uri, $action);
	}
	
	public static function put(string $uri, array|string|callable|null $action): self
	{
		return self::add('PUT', $uri, $action);
	}

	public static function delete(string $uri, array|string|callable|null $action): self
	{
		return self::add('DELETE', $uri, $action);
	}

	public static function patch(string $uri, array|string|callable|null $action): self
	{
		return self::add('PATCH', $uri, $action);
	}

	public static function options(string $uri, array|string|callable|null $action): self
	{
		return self::add('OPTIONS', $uri, $action);
	}

	public static function any(string $uri, array|string|callable|null $action): self
	{
		return self::add('ANY', $uri, $action);
	}

	public static function group(callable $callback): self
	{
		$callback();

		if (self::$middlewares) {
			self::$middlewares = [];
		}

		return new self;
	}

	public static function middleware(string|array $middlewares): self
	{
		self::$middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
		return new self;
	}

	public static function run(): void
	{
		$uri = $_SERVER['REQUEST_URI'];
		$uri = parse_url($uri, PHP_URL_PATH);
		$uri = explode('?', $uri)[0];
		$uri = trim($uri, '/');
		$uri = $uri ?: '/';
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
					self::handleAction($action);
				});
			}
		} else {
			self::handleAction($action);
		}
	}

	public static function getRoutes(): array
	{
		return self::$routes;
	}

	private static function handleAction(\Closure|array $action): void
	{
		if (is_callable($action)) {
			$action();
		} else {
			$controller = new $action['controller'];
			$method = $action['method'];
			$controller->$method();
		}
	}
}
