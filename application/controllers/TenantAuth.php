<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_DB $db
 * @property CI_Input $input
 * @property CI_Output $output
 */
class TenantAuth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->output->set_content_type('application/json');
	}

	public function login()
	{
		try {
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
			// No permitir login de admin por este endpoint de tenant
			if (isset($u->rol) && $u->rol === 'admin') {
				return $this->output->set_status_header(403)->set_output(json_encode(['ok' => false, 'msg' => 'Use el login de administrador']));
			}

			$token = jwt_issue($u->id, (int)$u->tenant_id, $u->rol, 60 * 60 * 8); // 8h
			$expire = time() + 60 * 60 * 8;
			$cookie_name = 'imenu_token';
			$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
			setcookie($cookie_name, $token, $expire, '/');
			$expires_gmt = gmdate('D, d M Y H:i:s T', $expire);
			$cookie_header = $cookie_name . '=' . $token . '; Expires=' . $expires_gmt . '; Path=/; HttpOnly; SameSite=Strict';
			if ($secure) $cookie_header .= '; Secure';
			header('Set-Cookie: ' . $cookie_header);

			// Retornar token en respuesta para compatibilidad con JavaScript
			return $this->output->set_output(json_encode([
				'ok' => true,
				'rol' => $u->rol,
				'tenant_id' => (int)$u->tenant_id,
				'token' => $token // Agregar el token en la respuesta
			]));
		} catch (Exception $e) {
			$this->output->set_status_header(500);
			return $this->output->set_output(json_encode(['ok' => false, 'msg' => 'Server error', 'error' => $e->getMessage()]));
		}
	}

	public function logout()
	{
		setcookie('imenu_token', '', time() - 3600, '/');
		header('Set-Cookie: imenu_token=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Path=/; HttpOnly; SameSite=Strict');
		return $this->output->set_output(json_encode(['ok' => true, 'msg' => 'Sesión cerrada']));
	}
}
