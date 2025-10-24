<?php defined('BASEPATH') or exit('No direct script access allowed');

class CategoriasService extends MY_Controller
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
		$this->load->model('Categoria_model', 'categoria_model');
	}

	// ===== Categorías =====
	public function categorias()
	{ // GET
		$tid = current_tenant_id();
		$rows = $this->categoria_model->get_by_tenant($tid);
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function categoria_create()
	{ // POST: nombre, orden
		$tid = current_tenant_id();
		$data = [
			'tenant_id' => $tid,
			'nombre' => $this->input->post('nombre', true),
			'orden' => (int)$this->input->post('orden')
		];
		$this->enforce_limits($tid, 'categorias');
		$id = $this->categoria_model->create($data);
		echo json_encode(['ok' => true, 'id' => $id]);
	}

	public function categoria_update($id)
	{ // POST
		$tid = current_tenant_id();
		$data = [];
		foreach (['nombre', 'orden', 'activo'] as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$this->categoria_model->update($id, $tid, $data);
		echo json_encode(['ok' => true]);
	}

	public function categoria_delete($id)
	{
		$tid = current_tenant_id();
		$this->categoria_model->delete($id, $tid);
		echo json_encode(['ok' => true]);
	}
}
