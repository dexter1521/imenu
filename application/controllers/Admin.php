<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Admin
 * Controlador para las páginas autenticadas del panel de administración.
 *
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 */
class Admin extends MY_Controller
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
		$this->load->model('Tenant_model', 'tenant_model');
		$this->load->model('Plan_model', 'plan_model');
		$this->load->model('Suscripcion_model', 'suscripcion_model');
		$this->load->model('Pago_model', 'pago_model');
		$this->load->model('Pedido_model', 'pedido_model');
	}
	
	// ===== Vistas del Panel Admin =====

	/**
	 * Vista del Dashboard principal
	 */
	public function dashboard()
	{
		$this->data['page_title'] = 'Dashboard';
		$this->render_admin_template('admin/dashboard');
	}

	public function tenants_view()
	{
		$this->data['page_title'] = 'Tenants';
		$this->render_admin_template('admin/tenants');
	}

	public function planes_view()
	{
		$this->data['page_title'] = 'Planes';
		$this->render_admin_template('admin/planes');
	}

	public function pagos_view()
	{
		$this->data['page_title'] = 'Pagos';
		$this->render_admin_template('admin/pagos');
	}

	public function suscripciones_view()
	{
		$this->data['page_title'] = 'Suscripciones';
		$this->render_admin_template('admin/suscripciones');
	}

	// ===== DASHBOARD =====

	/**
	 * Obtener estadísticas globales del dashboard
	 * GET /admin/dashboard_stats
	 */
	public function dashboard_stats()
	{
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede ver el dashboard');
			return;
		}

		// Recopilar estadísticas de todos los modelos
		$stats = [
			'tenants' => $this->tenant_model->get_dashboard_stats(),
			'planes' => $this->plan_model->get_dashboard_stats(),
			'planes_populares' => $this->plan_model->get_most_popular(5),
			'ingresos' => $this->pago_model->get_revenue_stats(),
			'pagos' => $this->pago_model->get_stats([]),
			'pedidos' => $this->pedido_model->get_global_stats(),
			'suscripciones' => $this->suscripcion_model->get_dashboard_stats(),
			'grafica_ingresos' => $this->pago_model->get_monthly_revenue(12)
		];

		// Calcular métricas adicionales
		$stats['metricas_generales'] = [
			'total_usuarios_sistema' => $stats['tenants']['total'],
			'tasa_retencion' => $stats['tenants']['total'] > 0
				? round(($stats['tenants']['activos'] / $stats['tenants']['total']) * 100, 2)
				: 0,
			'ingreso_promedio_por_tenant' => $stats['tenants']['activos'] > 0
				? round($stats['ingresos']['mes_actual'] / $stats['tenants']['activos'], 2)
				: 0,
			'proyeccion_mensual' => $stats['ingresos']['promedio_diario'] * 30
		];

		echo json_encode(['ok' => true, 'data' => $stats]);
	}
}
