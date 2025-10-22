<?php defined('BASEPATH') or exit('No direct script access allowed');

class App extends MY_Controller
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
		$this->load->model('Producto_model', 'producto_model');
		$this->load->model('Ajustes_model', 'ajustes_model');
		$this->load->model('Tenant_model', 'tenant_model');
		$this->load->model('Pedido_model', 'pedido_model');
	}

	// ===== Vistas del Panel =====
	public function dashboard()
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

	public function pedidos_view()
	{
		$this->data['page_title'] = 'Pedidos';
		$this->render_template('app/pedidos');
	}

	public function ajustes_view()
	{
		$this->data['page_title'] = 'Ajustes';
		$this->render_template('app/ajustes');
	}

	public function usuarios_view()
	{
		$this->data['page_title'] = 'Staff';
		$this->render_template('app/usuarios');
	}

	public function plan_view()
	{
		$this->data['page_title'] = 'Plan y Suscripción';
		$this->render_template('app/plan');
	}

	// ===== Dashboard =====
	// GET /app/dashboard_data - Estadísticas para el dashboard
	public function dashboard_data()
	{
		$tid = current_tenant_id();

		// Obtener fechas
		$hoy = date('Y-m-d');
		$inicio_hoy = $hoy . ' 00:00:00';
		$fin_hoy = $hoy . ' 23:59:59';

		// Pedidos de hoy
		$pedidos_hoy = $this->pedido_model->count_by_tenant($tid, [
			'fecha_inicio' => $inicio_hoy,
			'fecha_fin' => $fin_hoy
		]);

		// Ingresos de hoy
		$this->db->select('COALESCE(SUM(total), 0) as ingresos');
		$this->db->where('tenant_id', $tid);
		$this->db->where('creado_en >=', $inicio_hoy);
		$this->db->where('creado_en <=', $fin_hoy);
		$ingresos_hoy = $this->db->get('pedidos')->row()->ingresos;

		// Productos activos
		$productos_activos = $this->producto_model->count_by_tenant($tid, ['activo' => 1]);

		// Total categorías
		$total_categorias = $this->categoria_model->count_by_tenant($tid);

		// Información del plan y suscripción
		$tenant_info = $this->tenant_model->get_with_plan($tid);

		// Calcular días restantes de suscripción
		$dias_restantes = null;
		if (!empty($tenant_info->suscripcion_fin)) {
			$fecha_fin = new DateTime($tenant_info->suscripcion_fin);
			$fecha_actual = new DateTime();
			$diferencia = $fecha_actual->diff($fecha_fin);
			$dias_restantes = $diferencia->invert ? 0 : $diferencia->days;
		}

		// Pedidos recientes (últimos 5)
		$pedidos_recientes = $this->pedido_model->list_by_tenant($tid, [
			'limit' => 5,
			'order_by' => 'creado_en',
			'orden' => 'desc'
		]);

		// Calcular límites usados vs disponibles
		$limites = [
			'categorias' => [
				'usado' => $total_categorias,
				'limite' => $tenant_info->limite_categorias ?? null
			],
			'productos' => [
				'usado' => $productos_activos,
				'limite' => $tenant_info->limite_items ?? null
			]
		];

		$response = [
			'ok' => true,
			'stats' => [
				'pedidos_hoy' => (int)$pedidos_hoy,
				'ingresos_hoy' => (float)$ingresos_hoy,
				'productos_activos' => (int)$productos_activos,
				'total_categorias' => (int)$total_categorias
			],
			'plan' => [
				'nombre' => $tenant_info->plan_nombre ?? 'Sin plan',
				'dias_restantes' => $dias_restantes,
				'suscripcion_activa' => !empty($tenant_info->suscripcion_activa) ? (int)$tenant_info->suscripcion_activa : 0,
				'limites' => $limites
			],
			'pedidos_recientes' => $pedidos_recientes
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	// ===== Información del Tenant (DEBUG) =====
	public function tenant_info()
	{
		$tid = current_tenant_id();
		$tenant = $this->tenant_model->get($tid);
		
		$info = [
			'ok' => true,
			'tenant' => [
				'id' => $tenant->id,
				'nombre' => $tenant->nombre,
				'slug' => $tenant->slug ?? 'NO CONFIGURADO',
				'activo' => $tenant->activo,
				'url_menu_publico' => $tenant->slug ? base_url('r/' . $tenant->slug) : 'Slug no configurado'
			]
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($info));
	}

	// ===== Plan y Suscripción =====
	// GET /app/plan_info - Información del plan y uso actual
	public function plan_info()
	{
		$tid = current_tenant_id();

		// Obtener información del tenant con plan
		$tenant_info = $this->tenant_model->get_with_plan($tid);

		if (!$tenant_info) {
			$this->output
				->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(['ok' => false, 'msg' => 'Tenant no encontrado']));
			return;
		}

		// Calcular días restantes de suscripción
		$dias_restantes = null;
		$fecha_fin = null;
		if (!empty($tenant_info->suscripcion_fin)) {
			$fecha_fin = $tenant_info->suscripcion_fin;
			$fecha_fin_dt = new DateTime($tenant_info->suscripcion_fin);
			$fecha_actual = new DateTime();
			$diferencia = $fecha_actual->diff($fecha_fin_dt);
			$dias_restantes = $diferencia->invert ? 0 : $diferencia->days;
		}

		// Obtener uso actual
		$total_categorias = $this->categoria_model->count_by_tenant($tid);
		$total_productos = $this->producto_model->count_by_tenant($tid);
		$total_pedidos_mes = $this->pedido_model->count_by_tenant($tid, [
			'fecha_inicio' => date('Y-m-01 00:00:00'),
			'fecha_fin' => date('Y-m-t 23:59:59')
		]);

		// Límites según plan
		$limites = [
			'categorias' => $tenant_info->plan_limite_categorias ?? 0,
			'productos' => $tenant_info->plan_limite_productos ?? 0,
			'pedidos_mes' => $tenant_info->plan_limite_pedidos_mes ?? 0
		];

		// Calcular porcentajes de uso
		$uso_categorias = $limites['categorias'] > 0 
			? round(($total_categorias / $limites['categorias']) * 100, 1) 
			: 0;
		$uso_productos = $limites['productos'] > 0 
			? round(($total_productos / $limites['productos']) * 100, 1) 
			: 0;
		$uso_pedidos = $limites['pedidos_mes'] > 0 
			? round(($total_pedidos_mes / $limites['pedidos_mes']) * 100, 1) 
			: 0;

		$response = [
			'ok' => true,
			'plan' => [
				'id' => $tenant_info->plan_id ?? null,
				'nombre' => $tenant_info->plan_nombre ?? 'Sin plan',
				'precio' => $tenant_info->plan_precio ?? 0,
				'descripcion' => $tenant_info->plan_descripcion ?? '',
				'suscripcion_activa' => !empty($tenant_info->suscripcion_activa) ? 1 : 0,
				'fecha_fin' => $fecha_fin,
				'dias_restantes' => $dias_restantes
			],
			'limites' => $limites,
			'uso' => [
				'categorias' => $total_categorias,
				'productos' => $total_productos,
				'pedidos_mes' => $total_pedidos_mes
			],
			'porcentajes' => [
				'categorias' => $uso_categorias,
				'productos' => $uso_productos,
				'pedidos_mes' => $uso_pedidos
			]
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
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

	// ===== Productos =====
	public function productos()
	{ // GET
		$tid = current_tenant_id();
		$rows = $this->producto_model->get_by_tenant($tid);
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
		$tid = current_tenant_id();
		$allowed = ['categoria_id', 'nombre', 'descripcion', 'precio', 'img_url', 'orden', 'activo', 'destacado'];
		$data = [];
		foreach ($allowed as $k) {
			if (null !== ($v = $this->input->post($k))) $data[$k] = $v;
		}
		$this->producto_model->update($id, $tid, $data);
		echo json_encode(['ok' => true]);
	}

	public function producto_delete($id)
	{
		$tid = current_tenant_id();
		$this->producto_model->delete($id, $tid);
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

	// ===== Ajustes =====
	public function ajustes_get()
	{
		$tid = current_tenant_id();
		
		try {
			$row = $this->ajustes_model->get_by_tenant($tid);
			
			// Si no hay ajustes, crear valores por defecto
			if (!$row) {
				$row = (object)[
					'nombre_negocio' => '',
					'telefono' => '',
					'email' => '',
					'direccion' => '',
					'color_primario' => '#F50087',
					'mostrar_precios' => 1,
					'mostrar_imagenes' => 1,
					'aceptar_pedidos' => 1,
					'idioma' => 'es',
					'moneda' => 'MXN',
					'formato_precio' => '$0.00',
					'zona_horaria' => 'America/Mexico_City',
					'mensaje_bienvenida' => '',
					'notas_menu' => '',
					'mensaje_pedido' => '',
					'pie_menu' => ''
				];
			}
			
			echo json_encode(['ok' => true, 'data' => $row]);
		} catch (Exception $e) {
			log_message('error', 'Error en ajustes_get: ' . $e->getMessage());
			http_response_code(500);
			echo json_encode(['ok' => false, 'msg' => 'Error al cargar ajustes']);
		}
	}

	public function ajustes_update()
	{
		$tid = current_tenant_id();
		$data = [];
		
		// Información general
		$generalFields = [
			'nombre_negocio', 'telefono', 'email', 'direccion'
		];
		
		// Personalización visual
		$visualFields = [
			'color_primario', 'logo_url', 'mostrar_precios', 'mostrar_imagenes', 'aceptar_pedidos'
		];
		
		// Configuración regional
		$regionalFields = [
			'idioma', 'moneda', 'formato_precio', 'zona_horaria'
		];
		
		// Mensajes personalizados
		$messageFields = [
			'mensaje_bienvenida', 'notas_menu', 'mensaje_pedido', 'pie_menu'
		];
		
		// Horarios (7 días x 3 campos)
		$horarioFields = [];
		$dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
		foreach ($dias as $dia) {
			$horarioFields[] = $dia . '_abierto';
			$horarioFields[] = $dia . '_inicio';
			$horarioFields[] = $dia . '_fin';
		}
		
		// Unir todos los campos
		$allFields = array_merge($generalFields, $visualFields, $regionalFields, $messageFields, $horarioFields);
		
		// Recolectar datos del POST
		foreach ($allFields as $k) {
			if (null !== ($v = $this->input->post($k))) {
				$data[$k] = $v;
			}
		}
		
		// Guardar
		$this->ajustes_model->upsert($tid, $data);
		echo json_encode(['ok' => true]);
	}

	// ===== Límite por plan =====
	private function enforce_limits($tenant_id, $tipo)
	{
		// $tipo: 'categorias'|'productos'
		$plan = $this->tenant_model->get_with_plan($tenant_id);
		if (!$plan) return; // sin plan = sin límite
		if ($tipo === 'categorias') {
			$count = $this->categoria_model->count_by_tenant($tenant_id);
			if ($plan->limite_categorias && $count >= $plan->limite_categorias) {
				http_response_code(422);
				echo json_encode(['ok' => false, 'msg' => 'Límite de categorías alcanzado']);
				exit;
			}
		} else if ($tipo === 'productos') {
			$count = $this->producto_model->count_by_tenant($tenant_id);
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
		header('Content-Type: application/json');

		$tid = current_tenant_id();
		$this->load->model('Pedido_model');

		// Obtener filtros
		$filters = [
			'estado' => $this->input->get('estado', true),
			'fecha_inicio' => $this->input->get('fecha_inicio', true),
			'fecha_fin' => $this->input->get('fecha_fin', true),
			'cliente' => $this->input->get('cliente', true),
			'metodo_pago' => $this->input->get('metodo_pago', true),
			'limit' => (int)$this->input->get('limit') ?: 50,
			'offset' => (int)$this->input->get('offset') ?: 0,
			'orden' => $this->input->get('orden', true) ?: 'desc'
		];

		// Validar filtros
		if (!empty($filters['estado'])) {
			$estados_validos = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
			if (!in_array($filters['estado'], $estados_validos)) {
				$this->_api_error(400, 'Estado inválido');
				return;
			}
		}

		if ($filters['limit'] > 100) {
			$filters['limit'] = 100; // Límite máximo para evitar sobrecarga
		}

		try {
			$rows = $this->Pedido_model->list_by_tenant($tid, $filters);

			// Contar total para paginación
			$total = $this->Pedido_model->count_by_tenant($tid, $filters);

			echo json_encode([
				'ok' => true,
				'data' => $rows,
				'pagination' => [
					'total' => $total,
					'limit' => $filters['limit'],
					'offset' => $filters['offset'],
					'has_more' => ($filters['offset'] + $filters['limit']) < $total
				]
			]);
		} catch (Exception $e) {
			log_message('error', 'Error listando pedidos: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido_create()
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		$tid = current_tenant_id();
		$nombre_cliente = $this->input->post('nombre_cliente', true);
		$telefono_cliente = $this->input->post('telefono_cliente', true);
		$items = $this->input->post('items'); // JSON array

		// Validaciones de entrada
		if (empty($nombre_cliente) || empty($items)) {
			$this->_api_error(400, 'nombre_cliente e items son requeridos');
			return;
		}

		if (strlen($nombre_cliente) > 100) {
			$this->_api_error(400, 'nombre_cliente demasiado largo');
			return;
		}

		if (!empty($telefono_cliente) && strlen($telefono_cliente) > 20) {
			$this->_api_error(400, 'telefono_cliente demasiado largo');
			return;
		}

		// Validar y decodificar items
		if (is_string($items)) {
			$items = json_decode($items, true);
		}

		if (!is_array($items) || empty($items)) {
			$this->_api_error(400, 'items debe ser un array válido');
			return;
		}

		if (count($items) > 50) {
			$this->_api_error(400, 'Demasiados items en el pedido');
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$pedido_data = [
				'tenant_id' => $tid,
				'nombre_cliente' => $nombre_cliente,
				'telefono_cliente' => $telefono_cliente,
				'metodo_pago' => $this->input->post('metodo_pago', true) ?: 'efectivo',
				'estado' => 'pendiente'
			];

			$pedido_id = $this->Pedido_model->create_with_items($pedido_data, $items);

			if ($pedido_id) {
				// Trigger notification for new order
				$this->load->library('Notification_lib');
				$this->Notification_lib->notify_new_order($pedido_id, $tid);

				echo json_encode(['ok' => true, 'id' => $pedido_id]);
			} else {
				$this->_api_error(500, 'Error al crear el pedido');
			}
		} catch (Exception $e) {
			log_message('error', 'Error creando pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido($id)
	{
		header('Content-Type: application/json');

		$tid = current_tenant_id();
		$this->load->model('Pedido_model');
		$row = $this->Pedido_model->get_with_items($tid, (int)$id);

		if (!$row) {
			$this->_api_error(404, 'Pedido no encontrado');
			return;
		}

		// Verificar que el pedido pertenece al tenant actual
		if (!$this->_validate_tenant_access($row->tenant_id)) {
			return;
		}

		echo json_encode(['ok' => true, 'data' => $row]);
	}

	public function pedido_update_estado($id)
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		$tid = current_tenant_id();
		$estado = $this->input->post('estado', true);

		// Validar entrada
		if (empty($estado)) {
			$this->_api_error(400, 'Estado es requerido');
			return;
		}

		$estados_validos = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
		if (!in_array($estado, $estados_validos)) {
			$this->_api_error(400, 'Estado inválido. Valores permitidos: ' . implode(', ', $estados_validos));
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$updated = $this->Pedido_model->update_estado($tid, (int)$id, $estado);

			if ($updated) {
				echo json_encode(['ok' => true, 'msg' => 'Estado actualizado']);
			} else {
				$this->_api_error(404, 'Pedido no encontrado');
			}
		} catch (Exception $e) {
			log_message('error', 'Error actualizando estado de pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido_delete($id)
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		// Solo owner puede eliminar pedidos
		if (current_role() !== 'owner') {
			$this->_api_error(403, 'Solo el owner puede eliminar pedidos');
			return;
		}

		$tid = current_tenant_id();

		// Validar ID
		$pedido_id = (int)$id;
		if ($pedido_id <= 0) {
			$this->_api_error(400, 'ID de pedido inválido');
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$deleted = $this->Pedido_model->delete_pedido($tid, $pedido_id);

			if ($deleted) {
				echo json_encode(['ok' => true, 'msg' => 'Pedido eliminado']);
			} else {
				$this->_api_error(404, 'Pedido no encontrado');
			}
		} catch (Exception $e) {
			log_message('error', 'Error eliminando pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	// ===== Configuración de Notificaciones =====

	public function notificaciones_config()
	{
		header('Content-Type: application/json');

		// Verificar permisos de owner
		if (current_role() !== 'owner') {
			$this->_api_error(403, 'Solo el owner puede configurar notificaciones');
			return;
		}

		$tid = current_tenant_id();

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// Obtener configuración actual
			$config = $this->tenant_model->get_notification_config($tid);

			echo json_encode([
				'ok' => true,
				'data' => $config ?: [
					'notif_email' => null,
					'notif_webhook' => null,
					'notif_whatsapp' => null
				]
			]);
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Actualizar configuración
			$notif_email = $this->input->post('notif_email', true);
			$notif_webhook = $this->input->post('notif_webhook', true);
			$notif_whatsapp = $this->input->post('notif_whatsapp', true);

			// Validaciones
			if (!empty($notif_email) && !filter_var($notif_email, FILTER_VALIDATE_EMAIL)) {
				$this->_api_error(400, 'Email inválido');
				return;
			}

			if (!empty($notif_webhook) && !filter_var($notif_webhook, FILTER_VALIDATE_URL)) {
				$this->_api_error(400, 'URL de webhook inválida');
				return;
			}

			if (!empty($notif_whatsapp) && !preg_match('/^\+?[1-9]\d{1,14}$/', $notif_whatsapp)) {
				$this->_api_error(400, 'Número de WhatsApp inválido');
				return;
			}

			// Actualizar usando el modelo
			$update_data = [
				'notif_email' => $notif_email ?: null,
				'notif_webhook' => $notif_webhook ?: null,
				'notif_whatsapp' => $notif_whatsapp ?: null
			];

			try {
				$this->tenant_model->update_notification_config($tid, $update_data);
				echo json_encode(['ok' => true, 'msg' => 'Configuración actualizada']);
			} catch (Exception $e) {
				log_message('error', 'Error actualizando config notificaciones: ' . $e->getMessage());
				$this->_api_error(500, 'Error del servidor');
			}
		} else {
			$this->_api_error(405, 'Método no permitido');
		}
	}

	// ===== Exportación de Datos =====

	public function pedidos_export()
	{
		$tid = current_tenant_id();
		$formato = $this->input->get('formato', true) ?: 'csv';

		// Validar formato
		if (!in_array($formato, ['csv', 'json', 'excel'])) {
			$this->_api_error(400, 'Formato no soportado. Use: csv, json, excel');
			return;
		}

		// Obtener filtros (mismos que en pedidos())
		$filters = [
			'estado' => $this->input->get('estado', true),
			'fecha_inicio' => $this->input->get('fecha_inicio', true),
			'fecha_fin' => $this->input->get('fecha_fin', true),
			'cliente' => $this->input->get('cliente', true),
			'metodo_pago' => $this->input->get('metodo_pago', true),
			'limit' => 1000, // Límite para exportación
			'orden' => 'desc'
		];

		try {
			$this->load->model('Pedido_model');
			$pedidos = $this->Pedido_model->list_by_tenant($tid, $filters);

			if (empty($pedidos)) {
				$this->_api_error(404, 'No hay pedidos para exportar');
				return;
			}

			switch ($formato) {
				case 'csv':
					$this->_export_csv($pedidos);
					break;
				case 'json':
					$this->_export_json($pedidos);
					break;
				case 'excel':
					$this->_export_excel($pedidos);
					break;
			}
		} catch (Exception $e) {
			log_message('error', 'Error exportando pedidos: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	private function _export_csv($pedidos)
	{
		$filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		$output = fopen('php://output', 'w');

		// Encabezados
		fputcsv($output, [
			'ID',
			'Cliente',
			'Teléfono',
			'Total',
			'Método Pago',
			'Estado',
			'Items',
			'Fecha Creación'
		]);

		// Datos
		foreach ($pedidos as $pedido) {
			fputcsv($output, [
				$pedido->id,
				$pedido->nombre_cliente,
				$pedido->telefono_cliente,
				$pedido->total,
				$pedido->metodo_pago,
				$pedido->estado,
				$pedido->total_items,
				$pedido->creado_en
			]);
		}

		fclose($output);
	}

	private function _export_json($pedidos)
	{
		$filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.json';

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo json_encode([
			'exported_at' => date('c'),
			'total_records' => count($pedidos),
			'data' => $pedidos
		], JSON_PRETTY_PRINT);
	}

	private function _export_excel($pedidos)
	{
		// Fallback a CSV si no hay librería Excel
		$this->_export_csv($pedidos);
	}
}
