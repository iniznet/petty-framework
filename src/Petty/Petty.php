<?php

namespace Petty;

use Petty\Routing\Route;

class Petty
{
	public function boot(): void
	{
		$this->loadEnvironmentVariables();
		(new Route)::run();
	}

	private function loadEnvironmentVariables(): void
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
		$dotenv->load();
		$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
	}
}