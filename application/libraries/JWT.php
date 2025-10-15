<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT as FirebaseJWT;

class JWT {

    private $secret_key;

    public function __construct() {
        $this->secret_key = 'CHANGE_ME_SUPER_SECRET_32CHARS_MINIMO'; // Cambiar por un valor seguro
    }

    public function encode($payload) {
        return FirebaseJWT::encode($payload, $this->secret_key, 'HS256');
    }

    public function decode($token) {
        try {
            return FirebaseJWT::decode($token, $this->secret_key, ['HS256']);
        } catch (Exception $e) {
            throw new Exception('Token inválido: ' . $e->getMessage());
        }
    }
}
