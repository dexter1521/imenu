<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class App
 * Controlador para las páginas autenticadas del panel de administración.
 * Del lado del Tenant.
 */

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
		$pedidos_hoy = $this->pedido_model->count_by_tenant([
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
		$productos_activos = $this->producto_model->count_by_tenant(['activo' => 1]);

		// Total categorías
		$total_categorias = $this->categoria_model->count_by_tenant();

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
		$pedidos_recientes = $this->pedido_model->list_by_tenant([
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
		$total_categorias = $this->categoria_model->count_by_tenant();
		$total_productos = $this->producto_model->count_by_tenant();
		$total_pedidos_mes = $this->pedido_model->count_by_tenant([
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

	

	

	

	

	
}
