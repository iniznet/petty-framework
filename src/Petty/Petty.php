<?php

namespace Petty;

use Petty\Routing\Route;

class Petty
{
	public function boot(): void
	{
		$this->loadEnvironmentVariables();

		$this->runMigrations();
		$this->runSeeders();

		(new Route)::run();
	}

	private function loadEnvironmentVariables(): void
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
		$dotenv->load();
		$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
	}

	private function runMigrations(): void
	{
		$isNeedToRun = file_exists(BASE_PATH . '/.pettymigrate');

		if (!$isNeedToRun) {
			return;
		}

		$files = [];

		foreach(glob(BASE_PATH . '/database/migrations/*.php') as $migration) {
			$file = explode('/', $migration);
			$file = end($file);
			$file = str_replace('.php', '', $file);
			$files[] = $file;

			$migration = require_once $migration;

			try {
				$migration->up();
			} catch (\Exception $e) {
				throw new \Exception('Migration failed: ' . implode(', ', $files) . ' ' . $e->getMessage());
			}
		}

		unlink(BASE_PATH . '/.pettymigrate');
	}

	private function runSeeders(): void
	{
		$isNeedToRun = file_exists(BASE_PATH . '/.pettyseed');

		if (!$isNeedToRun) {
			return;
		}

		$files = [];

		// instantiate all seeders from BASE_PATH . '/database/seeders/*.php and run them
		foreach(glob(BASE_PATH . '/database/seeders/*.php') as $seeder) {
			require_once $seeder;

			$class = basename($seeder, '.php');
			$files[] = $class;

			try {
				$class = "\Database\Seeders\\{$class}";
				$seeder = new $class();
				$seeder->run();
			} catch (\Exception $e) {
				throw new \Exception('Seeder failed: ' . implode(', ', $files) . ' ' . $e->getMessage());
			}
		}

		unlink(BASE_PATH . '/.pettyseed');
	}
}