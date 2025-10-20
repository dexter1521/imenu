<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Admin
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

	// ===== Vistas del Panel Admin =====(fin)
	public function tenants()
	{
		$rows = $this->tenant_model->get_all();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function tenant_create()
	{
		// Solo admin global puede crear tenants
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede crear tenants');
			return;
		}
		$nombre = trim($this->input->post('nombre', true));
		if (!$nombre) {
			$this->_api_error(400, 'nombre requerido');
			return;
		}

		$slug = $this->input->post('slug', true);
		if (!$slug) {
			$slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nombre)));
		}
		// comprobar slug único
		if ($this->tenant_model->get_by_slug($slug)) {
			$this->_api_error(422, 'slug ya existe');
			return;
		}

		$data = [
			'nombre' => $nombre,
			'slug' => $slug,
			'logo_url' => $this->input->post('logo_url', true),
			'color_primario' => $this->input->post('color_primario', true),
			'color_secundario' => $this->input->post('color_secundario', true),
			'whatsapp' => $this->input->post('whatsapp', true),
			'activo' => (int)$this->input->post('activo') ?: 1,
			'plan_id' => (int)$this->input->post('plan_id') ?: null,
		];

		$tid = $this->tenant_model->insert($data);
		if (!$tid) {
			$this->_api_error(500, 'Error creando tenant');
			return;
		}
		// Crear registro de ajustes por defecto para el nuevo tenant
		$this->db->insert('ajustes', ['tenant_id' => $tid]);
		echo json_encode(['ok' => true, 'id' => $tid]);
	}

	public function tenant_update($id)
	{
		// Solo admin global puede actualizar tenants
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede actualizar tenants');
			return;
		}
		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}
		if (!$this->tenant_model->get($id)) {
			return $this->_api_error(404, 'Tenant no encontrado.');
		}

		$data = [];
		foreach (['nombre', 'slug', 'logo_url', 'color_primario', 'color_secundario', 'whatsapp', 'activo', 'plan_id'] as $k) {
			$v = $this->input->post($k, true);
			if ($v !== null) $data[$k] = $v;
		}

		// Normalizar 'activo' para que sea 0 o 1
		if (isset($data['activo'])) $data['activo'] = $data['activo'] ? 1 : 0;

		// Si el slug ha cambiado, verificar unicidad
		if (isset($data['slug'])) {
			if (!$this->tenant_model->is_slug_unique($data['slug'], $id)) {
				$this->_api_error(422, 'slug ya existe');
				return;
			}
		}
		if (!$this->tenant_model->update($id, $data)) {
			$this->_api_error(500, 'Error actualizando tenant');
			return;
		}
		echo json_encode(['ok' => true, 'msg' => 'Tenant actualizado correctamente.']);
	}

	public function tenant_delete($id)
	{
		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}
		// Solo admin global puede eliminar tenants
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede eliminar tenants');
			return;
		}
		if (!$this->tenant_model->get($id)) {
			return $this->_api_error(404, 'Tenant no encontrado.');
		}

		// La lógica de borrado en cascada se mueve al modelo
		if (!$this->tenant_model->delete_cascade($id)) {
			return $this->_api_error(500, 'Error al eliminar el tenant y sus datos asociados.');
		}

		// Opcional: Eliminar archivos físicos del tenant
		// $this->load->helper('file');
		// delete_files('./uploads/tenants/' . $id, TRUE);
		// @rmdir('./uploads/tenants/' . $id);
		echo json_encode(['ok' => true, 'msg' => 'Tenant y todos sus datos han sido eliminados.']);
	}

	/**
	 * Alterna el campo 'activo' de un tenant (activar / suspender)
	 */
	public function tenant_toggle($id)
	{
		// Solo admin global
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede cambiar estado de tenants');
			return;
		}
		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}
		$tenant = $this->tenant_model->get($id);
		if (!$tenant) {
			$this->_api_error(404, 'Tenant no encontrado');
			return;
		}
		$new = $tenant->activo ? 0 : 1;
		if (!$this->tenant_model->update($id, ['activo' => $new])) {
			$this->_api_error(500, 'Error actualizando estado');
			return;
		}
		echo json_encode(['ok' => true, 'msg' => 'Estado del tenant actualizado.', 'activo' => $new]);
	}

	/**
	 * Cambia el plan de un tenant
	 * POST /admin/tenant_change_plan/[id]
	 */
	public function tenant_change_plan($id)
	{
		// Solo admin global puede cambiar planes
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede cambiar planes de tenants');
			return;
		}

		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}

		$tenant = $this->tenant_model->get($id);
		if (!$tenant) {
			$this->_api_error(404, 'Tenant no encontrado');
			return;
		}

		$plan_id = (int)$this->input->post('plan_id');
		if ($plan_id <= 0) {
			$this->_api_error(400, 'plan_id requerido');
			return;
		}

		// Verificar que el plan existe
		$plan = $this->plan_model->get($plan_id);
		if (!$plan) {
			$this->_api_error(404, 'Plan no encontrado');
			return;
		}

		// Actualizar el plan del tenant
		if (!$this->tenant_model->update($id, ['plan_id' => $plan_id])) {
			$this->_api_error(500, 'Error actualizando plan del tenant');
			return;
		}

		echo json_encode([
			'ok' => true,
			'msg' => 'Plan actualizado correctamente a: ' . $plan->nombre,
			'plan_id' => $plan_id,
			'plan_nombre' => $plan->nombre
		]);
	}

	/**
	 * Muestra la ficha detallada de un tenant.
	 * GET /admin/tenant_show/[id]
	 */
	public function tenant_show($id)
	{
		$id = (int)$id;
		if ($id <= 0) {
			show_404();
			return;
		}

		$data = [];
		$data['tenant'] = $this->tenant_model->get($id);
		if (!$data['tenant']) {
			show_404();
			return;
		}

		// Información del plan actual
		$data['plan'] = null;
		if ($data['tenant']->plan_id) {
			$data['plan'] = $this->plan_model->get($data['tenant']->plan_id);
		}

		// Todos los planes disponibles (para el select de cambio de plan)
		$data['planes_disponibles'] = $this->plan_model->get_all();

		// Suscripción activa (la más reciente)
		$suscripcion = $this->suscripcion_model
			->where('tenant_id', $id)
			->order_by('fin', 'DESC')
			->get_one();
		$data['suscripcion'] = $suscripcion;

		// Últimos 10 pagos
		$data['ultimos_pagos'] = $this->pago_model
			->where('tenant_id', $id)
			->order_by('fecha', 'DESC')
			->limit(10)
			->get_all();

		// Últimos 10 pedidos
		$data['ultimos_pedidos'] = $this->pedido_model
			->where('tenant_id', $id)
			->order_by('fecha_creacion', 'DESC')
			->limit(10)
			->get_all();

		// Estadísticas básicas
		$this->load->model('Categoria_model', 'categoria_model');
		$this->load->model('Producto_model', 'producto_model');
		$data['stats'] = [
			'total_categorias' => $this->categoria_model->count_by_tenant($id),
			'total_productos' => $this->producto_model->count_by_tenant($id),
			'total_pedidos' => $this->pedido_model->where('tenant_id', $id)->get_all() ? count($this->pedido_model->where('tenant_id', $id)->get_all()) : 0,
			'total_pagos' => $this->pago_model->where('tenant_id', $id)->get_all() ? count($this->pago_model->where('tenant_id', $id)->get_all()) : 0,
		];

		// URLs útiles
		$data['qr_url'] = base_url('uploads/tenants/' . $id . '/qr.png');
		$data['menu_url'] = site_url('r/' . $data['tenant']->slug);

		// Título de la página
		$data['page_title'] = 'Ficha: ' . $data['tenant']->nombre;

		// Renderizar la vista
		$this->render_admin_template('admin/tenant_show', $data);
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

	// ===== PAGOS =====

	/**
	 * Listar pagos con filtros opcionales
	 * GET /admin/pagos
	 * Query params: tenant_id, status, metodo, fecha_inicio, fecha_fin, concepto
	 */
	public function pagos()
	{
		// Obtener parámetros de filtro
		$filters = [
			'tenant_id' => $this->input->get('tenant_id'),
			'status' => $this->input->get('status'),
			'metodo' => $this->input->get('metodo'),
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin'),
			'concepto' => $this->input->get('concepto')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		// Obtener pagos con filtros
		$rows = $this->pago_model->get_with_filters($filters);

		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	/**
	 * Obtener estadísticas de pagos
	 * GET /admin/pago_stats
	 * Query params: fecha_inicio, fecha_fin, tenant_id
	 */
	public function pago_stats()
	{
		$filters = [
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin'),
			'tenant_id' => $this->input->get('tenant_id')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		$stats = $this->pago_model->get_stats($filters);

		echo json_encode(['ok' => true, 'data' => $stats]);
	}

	/**
	 * Obtener detalles completos de un pago
	 * GET /admin/pago_detail/[id]
	 */
	public function pago_detail($id)
	{
		if (!$id) {
			$this->_api_error(400, 'ID de pago requerido');
			return;
		}

		$pago = $this->pago_model->get_with_relations((int)$id);

		if (!$pago) {
			$this->_api_error(404, 'Pago no encontrado');
			return;
		}

		echo json_encode(['ok' => true, 'data' => $pago]);
	}

	/**
	 * Exportar pagos a CSV o Excel
	 * GET /admin/pago_export
	 * Query params: formato (csv|excel), fecha_inicio, fecha_fin, tenant_id, status, metodo
	 */
	public function pago_export()
	{
		$formato = $this->input->get('formato') ?: 'csv';

		// Obtener filtros
		$filters = [
			'tenant_id' => $this->input->get('tenant_id'),
			'status' => $this->input->get('status'),
			'metodo' => $this->input->get('metodo'),
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		// Obtener datos
		$pagos = $this->pago_model->get_with_filters($filters);

		if ($formato === 'csv') {
			$this->_export_csv($pagos);
		} elseif ($formato === 'excel') {
			$this->_export_excel($pagos);
		} else {
			$this->_api_error(400, 'Formato no soportado. Use csv o excel');
		}
	}

	/**
	 * Exportar a CSV
	 */
	private function _export_csv($pagos)
	{
		$filename = 'pagos_' . date('Y-m-d_His') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		$output = fopen('php://output', 'w');

		// BOM para UTF-8 en Excel
		fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

		// Encabezados
		fputcsv($output, [
			'ID',
			'Tenant',
			'Slug',
			'Concepto',
			'Monto',
			'Método',
			'Referencia',
			'Estado',
			'Fecha',
			'Notas'
		]);

		// Datos
		foreach ($pagos as $pago) {
			fputcsv($output, [
				$pago->id,
				$pago->tenant_nombre ?? 'N/A',
				$pago->tenant_slug ?? 'N/A',
				$pago->concepto,
				number_format($pago->monto, 2),
				$pago->metodo,
				$pago->referencia ?? '',
				$pago->status,
				$pago->fecha,
				$pago->notas ?? ''
			]);
		}

		fclose($output);
		exit;
	}

	/**
	 * Exportar a Excel (CSV con formato mejorado)
	 */
	private function _export_excel($pagos)
	{
		$filename = 'pagos_' . date('Y-m-d_His') . '.xls';

		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
		echo '<head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
		echo '<x:Name>Pagos</x:Name>';
		echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>';
		echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
		echo '</head>';
		echo '<body>';
		echo '<table border="1">';

		// Encabezados
		echo '<thead><tr style="background-color: #4e73df; color: white; font-weight: bold;">';
		echo '<th>ID</th>';
		echo '<th>Tenant</th>';
		echo '<th>Slug</th>';
		echo '<th>Concepto</th>';
		echo '<th>Monto</th>';
		echo '<th>Método</th>';
		echo '<th>Referencia</th>';
		echo '<th>Estado</th>';
		echo '<th>Fecha</th>';
		echo '<th>Notas</th>';
		echo '</tr></thead>';

		// Datos
		echo '<tbody>';
		foreach ($pagos as $pago) {
			$statusColor = '';
			if ($pago->status === 'pagado') $statusColor = '#28a745';
			elseif ($pago->status === 'pendiente') $statusColor = '#ffc107';
			elseif ($pago->status === 'fallido') $statusColor = '#dc3545';

			echo '<tr>';
			echo '<td>' . htmlspecialchars($pago->id) . '</td>';
			echo '<td>' . htmlspecialchars($pago->tenant_nombre ?? 'N/A') . '</td>';
			echo '<td>' . htmlspecialchars($pago->tenant_slug ?? 'N/A') . '</td>';
			echo '<td>' . htmlspecialchars($pago->concepto) . '</td>';
			echo '<td style="text-align: right;">$' . number_format($pago->monto, 2) . '</td>';
			echo '<td>' . htmlspecialchars($pago->metodo) . '</td>';
			echo '<td>' . htmlspecialchars($pago->referencia ?? '') . '</td>';
			echo '<td style="background-color: ' . $statusColor . '; color: white;">' . htmlspecialchars($pago->status) . '</td>';
			echo '<td>' . htmlspecialchars($pago->fecha) . '</td>';
			echo '<td>' . htmlspecialchars($pago->notas ?? '') . '</td>';
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table>';
		echo '</body>';
		echo '</html>';

		exit;
	}

	// ===== SUSCRIPCIONES =====

	/**
	 * Listar todas las suscripciones
	 * GET /admin/suscripciones
	 */
	public function suscripciones()
	{
		$tenant_id = $this->input->get('tenant_id');

		if ($tenant_id) {
			// Filtrar por tenant
			$rows = $this->suscripcion_model->get_by_tenant((int)$tenant_id);
		} else {
			// Todas las suscripciones
			$rows = $this->suscripcion_model->get_all();
		}

		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	/**
	 * Crear nueva suscripción
	 * POST /admin/suscripcion_create
	 */
	public function suscripcion_create()
	{
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede crear suscripciones');
			return;
		}

		$tenant_id = (int)$this->input->post('tenant_id');
		$plan_id = (int)$this->input->post('plan_id');
		$inicio = $this->input->post('inicio');
		$fin = $this->input->post('fin');
		$estatus = $this->input->post('estatus') ?: 'activa';

		if (!$tenant_id || !$plan_id || !$inicio || !$fin) {
			$this->_api_error(400, 'tenant_id, plan_id, inicio y fin son requeridos');
			return;
		}

		// Validar que tenant y plan existen
		if (!$this->tenant_model->get($tenant_id)) {
			$this->_api_error(404, 'Tenant no encontrado');
			return;
		}

		if (!$this->plan_model->get($plan_id)) {
			$this->_api_error(404, 'Plan no encontrado');
			return;
		}

		$data = [
			'tenant_id' => $tenant_id,
			'plan_id' => $plan_id,
			'inicio' => $inicio,
			'fin' => $fin,
			'estatus' => $estatus
		];

		$id = $this->suscripcion_model->insert($data);
		if (!$id) {
			$this->_api_error(500, 'Error creando suscripción');
			return;
		}

		echo json_encode(['ok' => true, 'id' => $id, 'msg' => 'Suscripción creada correctamente']);
	}

	/**
	 * Actualizar suscripción existente
	 * POST /admin/suscripcion_update/[id]
	 */
	public function suscripcion_update($id)
	{
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede actualizar suscripciones');
			return;
		}

		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}

		$suscripcion = $this->suscripcion_model->get($id);
		if (!$suscripcion) {
			$this->_api_error(404, 'Suscripción no encontrada');
			return;
		}

		$data = [];

		// Campos actualizables
		$fields = ['plan_id', 'inicio', 'fin', 'estatus'];
		foreach ($fields as $field) {
			$value = $this->input->post($field);
			if ($value !== null) {
				$data[$field] = $value;
			}
		}

		if (empty($data)) {
			$this->_api_error(400, 'No hay datos para actualizar');
			return;
		}

		if (!$this->suscripcion_model->update($id, $data)) {
			$this->_api_error(500, 'Error actualizando suscripción');
			return;
		}

		echo json_encode(['ok' => true, 'msg' => 'Suscripción actualizada correctamente']);
	}

	/**
	 * Eliminar suscripción
	 * POST /admin/suscripcion_delete/[id]
	 */
	public function suscripcion_delete($id)
	{
		if (current_role() !== 'admin') {
			$this->_api_error(403, 'Solo admin puede eliminar suscripciones');
			return;
		}

		$id = (int)$id;
		if ($id <= 0) {
			$this->_api_error(400, 'ID inválido');
			return;
		}

		if (!$this->suscripcion_model->get($id)) {
			$this->_api_error(404, 'Suscripción no encontrada');
			return;
		}

		if (!$this->suscripcion_model->delete($id)) {
			$this->_api_error(500, 'Error eliminando suscripción');
			return;
		}

		echo json_encode(['ok' => true, 'msg' => 'Suscripción eliminada correctamente']);
	}

	/**
	 * Obtener histórico de suscripciones de un tenant
	 * GET /admin/tenant_suscripciones/[tenant_id]
	 */
	public function tenant_suscripciones($tenant_id)
	{
		$tenant_id = (int)$tenant_id;
		if ($tenant_id <= 0) {
			$this->_api_error(400, 'ID de tenant inválido');
			return;
		}

		$tenant = $this->tenant_model->get($tenant_id);
		if (!$tenant) {
			$this->_api_error(404, 'Tenant no encontrado');
			return;
		}

		// Obtener todas las suscripciones del tenant con información del plan
		$suscripciones = $this->db->select('s.*, p.nombre as plan_nombre, p.precio_mensual')
			->from('suscripciones s')
			->join('planes p', 'p.id = s.plan_id', 'left')
			->where('s.tenant_id', $tenant_id)
			->order_by('s.inicio', 'DESC')
			->get()
			->result();

		echo json_encode(['ok' => true, 'data' => $suscripciones, 'tenant' => $tenant]);
	}
}
