<?php

namespace Petty\Routing\Concerns;

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	exit;
}

trait Responses
{
	public static function response(array $data, int $status = 200): void
	{
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}
}