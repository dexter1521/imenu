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

		// Configurar vistas permitidas en el constructor
		$this->allowed_views = ['tenants_view', 'planes_view', 'pagos_view'];
		$this->validate_view_access();
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
		$rows = $this->db->get('tenants')->result();
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
		$exists = $this->db->get_where('tenants', ['slug' => $slug], 1)->row();
		if ($exists) {
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

		if (!$this->db->insert('tenants', $data)) {
			$this->_api_error(500, 'Error creando tenant');
			return;
		}
		/** @var CI_DB_driver $db_driver */
		$db_driver = $this->db;
		$tid = $db_driver->insert_id();
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
		$data = [];
		foreach (['nombre', 'slug', 'logo_url', 'color_primario', 'color_secundario', 'whatsapp', 'activo', 'plan_id'] as $k) {
			$v = $this->input->post($k, true);
			if ($v !== null) $data[$k] = $v;
		}
		// si slug cambiado, verificar unicidad
		if (isset($data['slug'])) {
			$exists = $this->db->where('slug', $data['slug'])->where('id !=', $id)->get('tenants')->row();
			if ($exists) {
				$this->_api_error(422, 'slug ya existe');
				return;
			}
		}
		if (!$this->db->update('tenants', $data, ['id' => $id])) {
			$this->_api_error(500, 'Error actualizando tenant');
			return;
		}
		echo json_encode(['ok' => true]);
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
		$this->db->delete('tenants', ['id' => $id]);
		// opcional: borrar ajustes y datos asociados (no destructivo por ahora)
		echo json_encode(['ok' => true]);
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
		$tenant = $this->db->get_where('tenants', ['id' => $id], 1)->row();
		if (!$tenant) {
			$this->_api_error(404, 'Tenant no encontrado');
			return;
		}
		$new = $tenant->activo ? 0 : 1;
		if (!$this->db->update('tenants', ['activo' => $new], ['id' => $id])) {
			$this->_api_error(500, 'Error actualizando estado');
			return;
		}
		echo json_encode(['ok' => true, 'activo' => $new]);
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
			if (!$this->db->update('planes', $data, ['id' => $id])) {
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
		if (!$this->db->delete('planes', ['id' => $id])) {
			$this->_api_error(500, 'Error eliminando plan');
			return;
		}
		echo json_encode(['ok' => true]);
	}

	// Planes
	public function planes()
	{
		$rows = $this->db->get('planes')->result();
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
		if (!$this->db->insert('planes', $data)) {
			$this->_api_error(500, 'Error creando plan');
			return;
		}
		/** @var CI_DB_driver $db_driver */
		$db_driver = $this->db;
		echo json_encode(['ok' => true, 'id' => $db_driver->insert_id()]);
	}

	// Pagos (lista simple)
	public function pagos()
	{
		$rows = $this->db->order_by('fecha', 'DESC')->get('pagos')->result();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}
}
