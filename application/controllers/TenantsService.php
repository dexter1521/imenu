<?php defined('BASEPATH') or exit('No direct script access allowed');


class TenantsService extends MY_Controller
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
}
