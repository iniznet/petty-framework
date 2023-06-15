<?php

namespace Petty\Routing;

use Petty\Http\Input;

class BaseController
{
	protected Input $input;

	public function __construct()
	{
		$this->input = new Input();
	}

	public function model(string $model): object
	{
		$model = 'App\\Models\\' . $model;
		return new $model;
	}
}