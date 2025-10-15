<?php

if (!function_exists('jwt_secret')) {
	function jwt_secret()
	{
		// Cambia a un valor largo aleatorio y guarda en .env/config
		return 'CHANGE_ME_SUPER_SECRET_32CHARS_MINIMO';
	}
}

if (!function_exists('jwt_from_request')) {
	function jwt_from_request()
	{
		$hdr = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['Authorization']) ? $_SERVER['Authorization'] : '');
		if (!$hdr && function_exists('apache_request_headers')) {
			$hs = apache_request_headers();
			if (isset($hs['Authorization'])) $hdr = $hs['Authorization'];
		}
		if (stripos($hdr, 'Bearer ') === 0) return trim(substr($hdr, 7));
		return null;
	}
}

if (!function_exists('jwt_issue')) {
	function jwt_issue($uid, $tenant_id, $rol, $ttl = 3600)
	{
		$now = time();
		$payload = [
			'iss' => base_url(),
			'sub' => $uid,
			'tenant_id' => $tenant_id,
			'rol' => $rol,
			'iat' => $now,
			'nbf' => $now,
			'exp' => $now + $ttl
		];
		$CI = &get_instance();
		$CI->load->library('JWT');
		return JWT::encode($payload, jwt_secret());
	}
}

if (!function_exists('jwt_require')) {
	function jwt_require($roles = null)
	{
		$CI = &get_instance();
		$CI->load->library('JWT');
		$token = jwt_from_request();
		if (!$token) {
			http_response_code(401);
			echo json_encode(['ok' => false, 'msg' => 'Falta Bearer token']);
			exit;
		}
		try {
			$payload = JWT::decode($token, jwt_secret());
		} catch (Exception $e) {
			http_response_code(401);
			echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
			exit;
		}
		if ($roles) {
			$roles = is_array($roles) ? $roles : [$roles];
			if (!in_array($payload['rol'], $roles)) {
				http_response_code(403);
				echo json_encode(['ok' => false, 'msg' => 'Rol no autorizado']);
				exit;
			}
		}
		$CI->jwt = (object)$payload; // disponible en controladores
	}
}

if (!function_exists('current_user_id')) {
	function current_user_id()
	{
		$CI = &get_instance();
		return isset($CI->jwt->sub) ? (int)$CI->jwt->sub : 0;
	}
}

if (!function_exists('current_tenant_id')) {
	function current_tenant_id()
	{
		$CI = &get_instance();
		return isset($CI->jwt->tenant_id) ? (int)$CI->jwt->tenant_id : 0;
	}
}

if (!function_exists('current_role')) {
	function current_role()
	{
		$CI = &get_instance();
		return isset($CI->jwt->rol) ? $CI->jwt->rol : null;
	}
}
