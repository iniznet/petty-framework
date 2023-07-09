<?php

namespace Petty\Database\Contracts;

interface SeedInterface
{
	public function group(callable $callback): void;
	
	public function run(): void;
}