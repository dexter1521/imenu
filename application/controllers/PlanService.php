<?php defined('BASEPATH') or exit('No direct script access allowed');


class PlanService extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Validar JWT directamente (no depender de AuthHook)
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
				redirect('/adminpanel/login?expired=1');
				exit;
			}
		}

		// Verificar que el usuario tenga rol de admin
		$rol = current_role();

		if ($rol !== 'admin') {
			if ($this->input->is_ajax_request()) {
				$this->_api_error(403, 'Acceso denegado: se requiere rol de administrador');
			} else {
				redirect('/adminpanel/login?expired=1');
			}
			exit;
		}

		// Cargar modelos necesarios
		$this->load->model('Plan_model', 'plan_model');
	}

	public function plan_update($id)
	{
		// Solo admin puede modificar planes
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede modificar planes');
			return;
		}
		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}
		$data = [];
		$nombre = $this->input->post('nombre', true);
		if ($nombre !== null) $data['nombre'] = trim($nombre);
		if (($v = $this->input->post('precio_mensual')) !== null) {
			$pv = filter_var($v, FILTER_VALIDATE_FLOAT);
			if ($pv === false) {
				$this->_api_error(422, 'precio_mensual inválido');
				return;
			}
			$data['precio_mensual'] = (float)$pv;
		}
		if (($v = $this->input->post('limite_categorias')) !== null) {
			$iv = filter_var($v, FILTER_VALIDATE_INT);
			if ($iv === false) {
				$this->_api_error(422, 'limite_categorias inválido');
				return;
			}
			$data['limite_categorias'] = (int)$iv;
		}
		if (($v = $this->input->post('limite_items')) !== null) {
			$iv = filter_var($v, FILTER_VALIDATE_INT);
			if ($iv === false) {
				$this->_api_error(422, 'limite_items inválido');
				return;
			}
			$data['limite_items'] = (int)$iv;
		}
		if (($v = $this->input->post('ads')) !== null) {
			$data['ads'] = (int)$v;
		}
		if (!empty($data)) {
			if (!$this->plan_model->update($id, $data)) {
				$this->_api_error(500, 'Error actualizando plan');
				return;
			}
		}
		echo json_encode(['ok' => true]);
	}

	public function plan_delete($id)
	{
		// Solo admin puede eliminar planes
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede eliminar planes');
			return;
		}
		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}
		if (!$this->plan_model->delete($id)) {
			$this->_api_error(500, 'Error eliminando plan');
			return;
		}
		echo json_encode(['ok' => true]);
	}

	// Planes
	public function planes()
	{
		$rows = $this->plan_model->get_all();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function plan_create()
	{
		// Solo admin puede crear planes
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede crear planes');
			return;
		}
		$nombre = trim($this->input->post('nombre', true));
		if (!$nombre) {
			$this->_api_error(400, 'nombre requerido');
			return;
		}
		$precio = $this->input->post('precio_mensual');
		$precio_f = filter_var($precio, FILTER_VALIDATE_FLOAT);
		if ($precio !== null && $precio_f === false) {
			$this->_api_error(422, 'precio_mensual inválido');
			return;
		}
		$data = [
			'nombre' => $nombre,
			'precio_mensual' => $precio_f !== false ? (float)$precio_f : 0.0,
			'limite_categorias' => (int)$this->input->post('limite_categorias'),
			'limite_items' => (int)$this->input->post('limite_items'),
			'ads' => (int)$this->input->post('ads')
		];
		$id = $this->plan_model->insert($data);
		if (!$id) {
			$this->_api_error(500, 'Error creando plan');
			return;
		}
		echo json_encode(['ok' => true, 'id' => $id]);
	}
}
