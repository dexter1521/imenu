<?php defined('BASEPATH') or exit('No direct script access allowed');

class App extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Para endpoints API que requieren autenticación JWT
		$view_methods = ['dashboard_view', 'categorias_view', 'productos_view', 'ajustes_view'];
		if (!in_array($this->router->fetch_method(), $view_methods)) {
			$this->output->set_content_type('application/json');
			jwt_require(['owner', 'staff']);
		}
	}

	// ===== Vistas del Panel =====
	public function dashboard_view()
	{
		$this->data['page_title'] = 'Dashboard';
		$this->render_template('app/dashboard');
	}

	public function categorias_view()
	{
		$this->data['page_title'] = 'Categorías';
		$this->render_template('app/categorias');
	}

	public function productos_view()
	{
		$this->data['page_title'] = 'Productos';
		$this->render_template('app/productos');
	}

	public function ajustes_view()
	{
		$this->data['page_title'] = 'Ajustes';
		$this->render_template('app/ajustes');
	}

	// GET /api/app/dashboard
	public function dashboard()
	{
		$tid = current_tenant_id();
		$c1 = $this->db->where('tenant_id', $tid)->count_all_results('categorias');
		$c2 = $this->db->where('tenant_id', $tid)->count_all_results('productos');
		$plan = $this->db->select('p.*')->from('tenants t')->join('planes p', 'p.id=t.plan_id', 'left')->where('t.id', $tid)->get()->row();
		echo json_encode(['ok' => true, 'stats' => ['categorias' => $c1, 'productos' => $c2], 'plan' => $plan]);
	}

	// ===== Categorías =====
	public function categorias()
	{ // GET
		$tid = current_tenant_id();
		$this->db->order_by('orden');
		$rows = $this->db->get_where('categorias', ['tenant_id' => $tid])->result();
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
		$this->db->insert('categorias', $data);
		echo json_encode(['ok' => true, 'id' => $this->db->insert_id()]);
	}

	public function categoria_update($id)
	{ // POST
		$tid = current_tenant_id();
		$data = [];
		foreach (['nombre', 'orden', 'activo'] as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$this->db->update('categorias', $data, ['id' => $id, 'tenant_id' => $tid]);
		echo json_encode(['ok' => true]);
	}

	public function categoria_delete($id)
	{
		$tid = current_tenant_id();
		$this->db->delete('categorias', ['id' => $id, 'tenant_id' => $tid]);
		echo json_encode(['ok' => true]);
	}

	// ===== Productos =====
	public function productos()
	{ // GET
		$tid = current_tenant_id();
		$this->db->order_by('orden');
		$rows = $this->db->get_where('productos', ['tenant_id' => $tid])->result();
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
		$this->db->insert('productos', $data);
		echo json_encode(['ok' => true, 'id' => $this->db->insert_id()]);
	}

	public function producto_update($id)
	{
		$tid = current_tenant_id();
		$allowed = ['categoria_id', 'nombre', 'descripcion', 'precio', 'img_url', 'orden', 'activo', 'destacado'];
		$data = [];
		foreach ($allowed as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$this->db->update('productos', $data, ['id' => $id, 'tenant_id' => $tid]);
		echo json_encode(['ok' => true]);
	}

	public function producto_delete($id)
	{
		$tid = current_tenant_id();
		$this->db->delete('productos', ['id' => $id, 'tenant_id' => $tid]);
		echo json_encode(['ok' => true]);
	}

	// ===== Ajustes =====
	public function ajustes_get()
	{
		$tid = current_tenant_id();
		$row = $this->db->get_where('ajustes', ['tenant_id' => $tid], 1)->row();
		echo json_encode(['ok' => true, 'data' => $row]);
	}

	public function ajustes_update()
	{
		$tid = current_tenant_id();
		$data = [];
		foreach (['idioma', 'moneda', 'formato_precio', 'notas', 'show_precios', 'show_imgs'] as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$exists = $this->db->get_where('ajustes', ['tenant_id' => $tid], 1)->row();
		if ($exists) $this->db->update('ajustes', $data, ['tenant_id' => $tid]);
		else {
			$data['tenant_id'] = $tid;
			$this->db->insert('ajustes', $data);
		}
		echo json_encode(['ok' => true]);
	}

	// ===== Límite por plan =====
	private function enforce_limits($tenant_id, $tipo)
	{
		// $tipo: 'categorias'|'productos'
		$plan = $this->db->select('p.*')->from('tenants t')->join('planes p', 'p.id=t.plan_id', 'left')->where('t.id', $tenant_id)->get()->row();
		if (!$plan) return; // sin plan = sin límite
		if ($tipo === 'categorias') {
			$count = $this->db->where('tenant_id', $tenant_id)->count_all_results('categorias');
			if ($plan->limite_categorias && $count >= $plan->limite_categorias) {
				http_response_code(422);
				echo json_encode(['ok' => false, 'msg' => 'Límite de categorías alcanzado']);
				exit;
			}
		} else if ($tipo === 'productos') {
			$count = $this->db->where('tenant_id', $tenant_id)->count_all_results('productos');
			if ($plan->limite_items && $count >= $plan->limite_items) {
				http_response_code(422);
				echo json_encode(['ok' => false, 'msg' => 'Límite de productos alcanzado']);
				exit;
			}
		}
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

	// --- Endpoints Usuarios ---
	public function usuarios_list()
	{
		$this->perms_required('stats'); // ver usuarios lo ligamos a ver estadísticas o puedes crear perm propio
		$tid = current_tenant_id();
		$this->load->model('User_model');
		$rows = $this->User_model->list_by_tenant($tid);
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
		$this->load->model(['User_model', 'Permission_model']);
		$uid = $this->User_model->create_staff($tid, $nombre, $email, $pwd);

		// Permisos iniciales (todo apagado excepto ver stats)
		$perms = [
			'can_products'   => (int)$this->input->post('can_products')   ?: 0,
			'can_categories' => (int)$this->input->post('can_categories') ?: 0,
			'can_adjustments' => (int)$this->input->post('can_adjustments') ?: 0,
			'can_view_stats' => (int)$this->input->post('can_view_stats') ?: 1,
		];
		$this->Permission_model->upsert($uid, $tid, $perms);

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
		$this->load->model(['User_model', 'Permission_model']);
		if (!empty($data)) $this->User_model->update_user($tid, $id, $data);

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
		$this->load->model('User_model');
		$this->User_model->delete_user($tid, $id);
		echo json_encode(['ok' => true]);
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

	public function pedidos()
	{
		$tid = current_tenant_id();
		$this->load->model('Pedido_model');
		$rows = $this->Pedido_model->list_by_tenant($tid);
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function pedido($id)
	{
		$tid = current_tenant_id();
		$this->load->model('Pedido_model');
		$row = $this->Pedido_model->get_with_items($tid, (int)$id);
		if (!$row) {
			http_response_code(404);
			echo json_encode(['ok' => false, 'msg' => 'No encontrado']);
			return;
		}
		echo json_encode(['ok' => true, 'data' => $row]);
	}
}
