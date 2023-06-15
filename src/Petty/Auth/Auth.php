<?php

namespace Petty\Auth;

use Petty\Database\QueryBuilder as DB;

class Auth
{
	public static function attempt(string $email, string $password): bool
	{
		$user = DB::table('users')->where('email', $email)->first();

		if (!$user) {
			return false;
		}

		if (!password_verify($password, $user->password)) {
			return false;
		}

		session_start();
		$_SESSION['user'] = $user;
		session_write_close();
		return true;
	}

	public static function user(): object
	{
		session_start();
		return $_SESSION['user'];
	}

	public static function check(): bool
	{
		session_start();
		return isset($_SESSION['user']);
	}

	public static function logout(): void
	{
		session_start();
		unset($_SESSION['user']);
		session_destroy();
	}
}