<?php

namespace Petty\Database\Contracts;

abstract class Seed implements SeedInterface
{
	public function group(callable $callback): void
	{
		$callback($this);
	}
}