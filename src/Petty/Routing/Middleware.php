<?php

namespace Petty\Routing;

class Middleware
{
	use Concerns\Responses;

	public function handle(\Closure $next): void
	{
		$next();
	}
}