<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/PlanLimitsTrait.php';

class ProductosService extends MY_Controller
{
	use PlanLimitsTrait;

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
		$this->load->model('Producto_model', 'producto_model');
		$this->load->model('Categoria_model', 'categoria_model');
		$this->load->model('Tenant_model', 'tenant_model');
	}

	// ===== Productos =====
	public function productos()
	{ // GET
		$rows = $this->producto_model->get_by_tenant();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function producto_create()
	{ // POST campos básicos
		$tid = current_tenant_id();
		$data = [
			'tenant_id' => $tid,
			'categoria_id' => (int)$this->input->post('categoria_id'),
			'nombre' => $this->input->post('nombre', true),
			'descripcion' => $this->input->post('descripcion', true),
			'precio' => (float)$this->input->post('precio'),
			'img_url' => $this->input->post('img_url', true),
			'orden' => (int)$this->input->post('orden'),
			'activo' => (int)$this->input->post('activo', true) ?: 1,
			'destacado' => (int)$this->input->post('destacado', true) ?: 0,
		];
		$this->enforce_limits($tid, 'productos');
		$id = $this->producto_model->create($data);
		echo json_encode(['ok' => true, 'id' => $id]);
	}

	public function producto_update($id)
	{
		$allowed = ['categoria_id', 'nombre', 'descripcion', 'precio', 'img_url', 'orden', 'activo', 'destacado'];
		$data = [];
		foreach ($allowed as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$this->producto_model->update($id, $data);
		echo json_encode(['ok' => true]);
	}

	public function producto_delete($id)
	{
		$this->producto_model->delete($id);
		echo json_encode(['ok' => true]);
	}

	/**
	 * Subir imagen de producto (multipart/form-data)
	 * Campo esperado: product_image
	 * Respuesta: { ok: true, url: '.../uploads/tenants/{tid}/productos/filename.ext' }
	 */
	public function producto_upload()
	{
		$tenant_id = current_tenant_id();
		$this->output->set_content_type('application/json');

		if (!$tenant_id) {
			http_response_code(403);
			echo json_encode(['ok' => false, 'msg' => 'Sin tenant asociado']);
			return;
		}

		$upload_path = FCPATH . 'uploads/tenants/' . $tenant_id . '/productos/';
		if (!is_dir($upload_path)) {
			@mkdir($upload_path, 0775, true);
		}

		$config = [
			'upload_path'   => $upload_path,
			'allowed_types' => 'jpg|jpeg|png|webp',
			'file_ext_tolower' => true,
			'overwrite'     => false,
			'max_size'      => 5120, // 5 MB
			'encrypt_name'  => true,
		];

		$this->load->library('upload', $config);

		$field = 'product_image';
		if (!$this->upload->do_upload($field)) {
			$err = strip_tags($this->upload->display_errors('', ''));
			http_response_code(422);
			echo json_encode(['ok' => false, 'msg' => $err]);
			return;
		}

		$data = $this->upload->data();
		$url = base_url('uploads/tenants/' . $tenant_id . '/productos/' . $data['file_name']);
		echo json_encode(['ok' => true, 'url' => $url]);
	}
}
