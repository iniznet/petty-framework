<?php

namespace Petty;

use Petty\Routing\Route;

class Petty
{
	public function boot(): void
	{
		(new Route)::run();
	}
}