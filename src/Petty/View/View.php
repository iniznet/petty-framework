<?php

namespace Petty\View;

class View
{
	protected static string $basePath = RESOURCES_PATH . '/views';

	public static function render(string $view, array $data = []): void
	{
		$view = self::$basePath . '/' . $view;
		$view = str_replace('.', '/', $view);
		$view .= '.php';

		if (!file_exists($view)) {
			throw new \Exception('View not found');
		}

		ob_start();
		extract($data);
		include_once $view;
		$content = ob_get_clean();

		echo $content ?: '';
	}

	public function __invoke(string $view, array $data = []): void
	{
		self::render($view, $data);
	}
}