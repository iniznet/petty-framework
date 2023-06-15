<?php

namespace Petty\Database;

class Seeder
{
	private string $path = BASE_PATH . '/database/seeders/';
	protected int $max = 100;
	protected array $seeders = [];

	public function add(string|array $seeders): void
	{
		if (is_array($seeders)) {
			$this->seeders = array_merge($this->seeders, $seeders);
		} else {
			$this->seeders[] = $seeders;
		}
	}

	public function group(callable $callback): void
	{
		$callback($this);
	}

	public function run(): void
	{
		foreach ($this->seeders as $seeder) {
			$seeder = $this->path . $seeder . '.php';

			if (!file_exists($seeder)) {
				throw new \Exception('Seeder not found');
			}

			$seeder = str_replace('/', '\\', $seeder);
			$seeder = str_replace('.php', '', $seeder);
			$seeder = new $seeder();

			if (!$seeder instanceof \Petty\Database\Seeder) {
				throw new \Exception('Seeder must be an instance of \Petty\Database\Seeder');
			}

			for ($i = 0; $i < $this->max; $i++) {
				$seeder->run();
			}
		}
	}
}