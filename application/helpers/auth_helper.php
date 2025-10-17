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
		// Buscar Authorization en varias variables servidoras y cabeceras
		$possible = [
			'HTTP_AUTHORIZATION',
			'Authorization',
			'REDIRECT_HTTP_AUTHORIZATION',
			'HTTP_X_AUTHORIZATION',
			'HTTP_X_FORWARDED_AUTHORIZATION'
		];
		$hdr = '';
		foreach ($possible as $k) {
			if (!empty($_SERVER[$k])) { $hdr = $_SERVER[$k]; break; }
		}
		// apache_request_headers (case-insensitive)
		if (!$hdr && function_exists('apache_request_headers')) {
			$hs = apache_request_headers();
			foreach ($hs as $hk => $hv) {
				if (strtolower($hk) === 'authorization') { $hdr = $hv; break; }
			}
		}
		if ($hdr && preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) {
			return trim($m[1]);
		}

		// Fallback: permitir token via cookie (ej. frontend lo almacena en localStorage y setea cookie)
		if (isset($_COOKIE['imenu_token']) && $_COOKIE['imenu_token']) {
			return trim($_COOKIE['imenu_token']);
		}
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
		// usar la instancia wrapper creada por la librería
		if (isset($CI->jwt) && method_exists($CI->jwt, 'encode')) {
			return $CI->jwt->encode($payload);
		}
		// fallback: intentar usar la clase Firebase directamente
		if (class_exists('\\Firebase\\JWT\\JWT')) {
			return \Firebase\JWT\JWT::encode($payload, jwt_secret(), 'HS256');
		}
		throw new Exception('No se pudo emitir el JWT: librería no encontrada');
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
			if (isset($CI->jwt) && method_exists($CI->jwt, 'decode')) {
				$payload = $CI->jwt->decode($token);
			} else {
				$payload = \Firebase\JWT\JWT::decode($token, jwt_secret(), ['HS256']);
			}
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
