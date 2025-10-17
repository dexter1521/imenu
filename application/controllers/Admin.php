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
		// NOTA: He añadido los modelos necesarios para las nuevas funcionalidades.
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Verificar que el usuario tenga rol de admin antes de continuar
		if (!isset($this->jwt->rol) || $this->jwt->rol !== 'admin') {
			if ($this->input->is_ajax_request()) {
				$this->_api_error(403, 'Acceso denegado: se requiere rol de administrador');
			} else {
				redirect('/adminpanel/login?expired=1');
			}
			exit;
		}

		// Configurar vistas permitidas en el constructor
		$this->allowed_views = ['tenants_view', 'planes_view', 'pagos_view'];
		$this->validate_view_access();
		$this->load->model('Tenant_model', 'tenant_model');
		$this->load->model('Plan_model', 'plan_model');
		$this->load->model('Suscripcion_model', 'suscripcion_model');
		$this->load->model('Pago_model', 'pago_model');
		$this->load->model('Pedido_model', 'pedido_model');
		$this->load->model('Ajustes_model', 'ajustes_model');
	}

	// ===== Vistas del Panel Admin =====
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

	// Tenants
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
		// Crear ajustes por defecto usando el modelo
		$this->ajustes_model->create_default($tid);
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
	 * Muestra la ficha detallada de un tenant.
	 * GET /admin/tenant_show/[id]
	 */
	public function tenant_show($id)
	{
		$data['tenant'] = $this->tenant_model->get($id);
		if (!$data['tenant']) {
			show_404();
		}

		$data['plan'] = $this->plan_model->get($data['tenant']->plan_id);
		$data['suscripcion'] = $this->suscripcion_model->where('tenant_id', $id)->order_by('fin', 'DESC')->get();
		$data['ultimos_pagos'] = $this->pago_model->where('tenant_id', $id)->limit(5)->order_by('fecha', 'DESC')->get_all();
		$data['ultimos_pedidos'] = $this->pedido_model->where('tenant_id', $id)->limit(5)->order_by('fecha_creacion', 'DESC')->get_all();
		$data['qr_url'] = base_url('uploads/tenants/' . $id . '/qr.png'); // Asumiendo que el QR se genera en esa ruta
		$data['menu_url'] = site_url('r/' . $data['tenant']->slug);

		// Carga la vista de la ficha (debes crear este archivo)
		$this->render_admin_template('admin/tenant_show_view', $data);
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

	// Pagos (lista simple)
	public function pagos()
	{
		$rows = $this->pago_model->order_by('fecha', 'DESC')->get_all();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}
}
