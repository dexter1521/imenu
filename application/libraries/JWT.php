<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Asegurarse de que Composer autoload esté incluido para cargar Firebase\JWT
if (!class_exists('Firebase\\JWT\\JWT')) {
	$autoloadPath = FCPATH . 'vendor/autoload.php';
	if (file_exists($autoloadPath)) {
		require_once $autoloadPath;
	}
}

use \Firebase\JWT\JWT as FirebaseJWT;

class JWT
{

	private $secret_key;

	public function __construct()
	{
		$this->secret_key = 'ingDLMRuGe9UKHRNjs7cYckS2yul4lc3'; // Cambiar por un valor seguro
	}

	public function encode($payload)
	{
		return FirebaseJWT::encode($payload, $this->secret_key, 'HS256');
	}

	public function decode($token)
	{
		try {
			// firebase/php-jwt v6 uses Key object; v5 uses signature (token, key, array)
			if (class_exists('Firebase\\JWT\\Key')) {
				return FirebaseJWT::decode($token, new \Firebase\JWT\Key($this->secret_key, 'HS256'));
			}
			return FirebaseJWT::decode($token, $this->secret_key, ['HS256']);
		} catch (Exception $e) {
			throw new Exception('Token inválido: ' . $e->getMessage());
		}
	}
}
