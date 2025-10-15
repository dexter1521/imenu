<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->output->set_content_type('application/json');
	}

	public function login()
	{
		$email = $this->input->post('email');
		$pass  = $this->input->post('password');
		if (!$email || !$pass) {
			return $this->output->set_status_header(400)->set_output(json_encode(['ok' => false, 'msg' => 'email/password requeridos']));
		}
		$q = $this->db->get_where('users', ['email' => $email, 'activo' => 1], 1);
		$u = $q->row();
		if (!$u || !password_verify($pass, $u->password)) {
			return $this->output->set_status_header(401)->set_output(json_encode(['ok' => false, 'msg' => 'Credenciales inválidas']));
		}
		$token = jwt_issue($u->id, (int)$u->tenant_id, $u->rol, 60 * 60 * 8); // 8h
		return $this->output->set_output(json_encode(['ok' => true, 'token' => $token, 'rol' => $u->rol, 'tenant_id' => (int)$u->tenant_id]));
	}

	public function logout()
	{
		// Invalidate the JWT token (implementation depends on your token handling strategy)
		return $this->output->set_output(json_encode(['ok' => true, 'msg' => 'Sesión cerrada correctamente']));
	}
}
