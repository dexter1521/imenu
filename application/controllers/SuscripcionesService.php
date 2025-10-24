<?php defined('BASEPATH') or exit('No direct script access allowed');

class SuscripcionesService extends MY_Controller
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
		$this->load->model('Suscripcion_model', 'suscripcion_model');
		$this->load->model('Tenant_model', 'tenant_model');
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
