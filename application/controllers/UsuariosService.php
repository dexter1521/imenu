<?php defined('BASEPATH') or exit('No direct script access allowed');

class UsuariosService extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Obtener el método que se está ejecutando
		$method = $this->router->fetch_method();

		// Métodos públicos que no requieren autenticación
		$public_methods = ['login'];

		// Si es un método público, no validar JWT
		if (in_array($method, $public_methods)) {
			return;
		}

		// Validar JWT directamente para métodos protegidos
		try {
			jwt_require(); // Esto establece $this->jwt
		} catch (Exception $e) {
			// JWT inválido o no existe
			if ($this->input->is_ajax_request()) {
				$this->output
					->set_status_header(401)
					->set_content_type('application/json')
					->set_output(json_encode(['ok' => false, 'msg' => 'No autenticado']))
					->_display();
				exit;
			} else {
				redirect('/app/login?expired=1');
				exit;
			}
		}

		// Verificar que el usuario tenga un tenant_id válido
		$tenant_id = current_tenant_id();
		if (!$tenant_id) {
			if ($this->input->is_ajax_request()) {
				$this->output
					->set_status_header(403)
					->set_content_type('application/json')
					->set_output(json_encode(['ok' => false, 'msg' => 'Sin tenant asociado']))
					->_display();
				exit;
			} else {
				redirect('/app/login?expired=1');
				exit;
			}
		}

		// Cargar modelos necesarios con alias en minúsculas
		$this->load->model('User_model', 'user_model');
		$this->load->model('Permission_model', 'permission_model');
	}

	// --- Endpoints Usuarios ---
	public function usuarios_list()
	{
		$this->perms_required('stats'); // ver usuarios lo ligamos a ver estadísticas o puedes crear perm propio
		$tid = current_tenant_id();
		$rows = $this->user_model->list_by_tenant($tid);
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function usuario_create()
	{
		// Solo owner puede crear
		if (current_role() !== 'owner') {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Solo owner puede crear usuarios']);
			return;
		}
		$tid = current_tenant_id();
		$nombre = $this->input->post('nombre', true);
		$email  = $this->input->post('email', true);
		if (!$email) {
			http_response_code(400);
			echo json_encode(['ok' => false, 'msg' => 'email requerido']);
			return;
		}

		// Generar password y crear
		$pwd = $this->random_password(10);
		$uid = $this->user_model->create_staff($tid, $nombre, $email, $pwd);

		// Permisos iniciales (todo apagado excepto ver stats)
		$perms = [
			'can_products'   => (int)$this->input->post('can_products')   ?: 0,
			'can_categories' => (int)$this->input->post('can_categories') ?: 0,
			'can_adjustments' => (int)$this->input->post('can_adjustments') ?: 0,
			'can_view_stats' => (int)$this->input->post('can_view_stats') ?: 1,
		];
		$this->permission_model->upsert($uid, $tid, $perms);

		// Enviar correo con CI Email
		$this->load->library('email');
		$this->email->from('no-reply@imenu.com.mx', 'iMenu');
		$this->email->to($email);
		$this->email->subject('Tu acceso a iMenu');
		$this->email->message("Hola $nombre,\n\nTu acceso a iMenu ha sido creado.\nEmail: $email\nContraseña: $pwd\n\nInicia sesión en el panel para comenzar.\n\n-- iMenu");
		@$this->email->send(); // silenciar en caso de fallo, puedes manejar errores si prefieres

		echo json_encode(['ok' => true, 'id' => $uid]);
	}

	public function usuario_update($id)
	{
		// owner puede actualizar rol/activo y permisos; staff no
		if (current_role() !== 'owner') {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Solo owner puede actualizar']);
			return;
		}
		$tid = current_tenant_id();
		$data = [];
		foreach (['nombre', 'email', 'activo'] as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}

		if (!empty($data)) $this->user_model->update_user($tid, $id, $data);

		$perms = [];
		foreach (['can_products', 'can_categories', 'can_adjustments', 'can_view_stats'] as $k) {
			if (null !== ($v = $this->input->post($k))) $perms[$k] = (int)$v;
		}
		if ($perms) $this->Permission_model->upsert($id, $tid, $perms);

		echo json_encode(['ok' => true]);
	}

	public function usuario_delete($id)
	{
		if (current_role() !== 'owner') {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Solo owner puede eliminar']);
			return;
		}
		$tid = current_tenant_id();
		$this->user_model->delete_user($tid, $id);
		echo json_encode(['ok' => true]);
	}

	// ===== Actualización: application/controllers/App.php (módulo usuarios + permisos granulares) =====
	// Agregar dentro de la clase App ya existente
	// --- Helpers internos ---

	private function perms_required($perm)
	{
		// owner siempre puede todo
		if (current_role() === 'owner') return;
		$this->load->model('Permission_model');
		$permRow = $this->Permission_model->get_by_user(current_user_id(), current_tenant_id());
		$map = [
			'products' => 'can_products',
			'categories' => 'can_categories',
			'adjustments' => 'can_adjustments',
			'stats' => 'can_view_stats',
		];
		$col = isset($map[$perm]) ? $map[$perm] : null;
		if (!$col || !$permRow || (int)$permRow->$col !== 1) {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Permiso insuficiente: ' . $perm]);
			exit;
		}
	}

	private function random_password($len = 10)
	{
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@$%&*';
		$out = '';
		for ($i = 0; $i < $len; $i++) {
			$out .= $chars[random_int(0, strlen($chars) - 1)];
		}
		return $out;
	}

	public function permisos_get($user_id)
	{
		if (current_role() !== 'owner') {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Solo owner']);
			return;
		}
		$tid = current_tenant_id();
		$this->load->model('Permission_model');
		$row = $this->Permission_model->get_by_user($user_id, $tid);
		echo json_encode(['ok' => true, 'data' => $row]);
	}

	public function permisos_update($user_id)
	{
		if (current_role() !== 'owner') {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Solo owner']);
			return;
		}
		$tid = current_tenant_id();
		$data = [];
		foreach (['can_products', 'can_categories', 'can_adjustments', 'can_view_stats'] as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = (int)$v;
		}
		$this->load->model('Permission_model');
		$this->Permission_model->upsert($user_id, $tid, $data);
		echo json_encode(['ok' => true]);
	}
}
