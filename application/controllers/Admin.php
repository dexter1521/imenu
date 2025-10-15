<?php defined('BASEPATH') or exit('No direct script access allowed');

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
		$nombre = $this->input->post('nombre', true);
		$slug   = $this->input->post('slug', true) ?: preg_replace('/[^a-z0-9\-]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nombre)));
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
		$this->db->insert('tenants', $data);
		$tid = $this->db->insert_id();
		$this->db->insert('ajustes', ['tenant_id' => $tid]);
		echo json_encode(['ok' => true, 'id' => $tid]);
	}

	// Planes
	public function planes()
	{
		$rows = $this->db->get('planes')->result();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	public function plan_create()
	{
		$data = [
			'nombre' => $this->input->post('nombre', true),
			'precio_mensual' => (float)$this->input->post('precio_mensual'),
			'limite_categorias' => (int)$this->input->post('limite_categorias'),
			'limite_items' => (int)$this->input->post('limite_items'),
			'ads' => (int)$this->input->post('ads')
		];
		$this->db->insert('planes', $data);
		echo json_encode(['ok' => true, 'id' => $this->db->insert_id()]);
	}

	// Pagos (lista simple)
	public function pagos()
	{
		$rows = $this->db->order_by('fecha', 'DESC')->get('pagos')->result();
		echo json_encode(['ok' => true, 'data' => $rows]);
	}
}
