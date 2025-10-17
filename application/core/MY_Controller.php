<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class MY_Controller
 * Controlador base centralizado para iMenu
 *
 * - Valida sesión automáticamente.
 * - Centraliza el renderizado de vistas.
 * - Proporciona métodos para validar tenant y permisos.
 * - Funciona con autenticación basada en sesiones (no JWT).
 */

/**
 * Class MY_Controller
 *
 * @property CI_Session $session
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Output $output
 */

class MY_Controller extends CI_Controller
{
	/**
	 * Datos globales para las vistas
	 * @var array
	 */
	protected $data = [];

	/**
	 * Lista de vistas permitidas por controlador (seguridad granular)
	 * @var array
	 */
	protected $allowed_views = [];

	public function __construct()
	{
		parent::__construct();

		// Métodos que no requieren autenticación (por ejemplo login)
		$excluded_methods = ['login', 'do_login', 'forgot_password'];

		$current_method = $this->router->fetch_method();
		if (!in_array($current_method, $excluded_methods)) {
			if (!$this->_verify_auth()) {
				exit;
			}
		}

		// Datos comunes para todas las vistas
		$this->data['page_title'] = 'iMenu';
		$this->data['user_name'] = $this->session->userdata('user_name') ?? 'Usuario';
	}

	/**
	 * Verifica que el usuario esté autenticado mediante sesión
	 */
	protected function _verify_auth()
	{
		// Verificar si existe sesión válida
		if (!$this->session->userdata('logged_in')) {
			if ($this->input->is_ajax_request()) {
				return $this->_api_error(401, 'Sesión no válida o expirada');
			} else {
				redirect('/tenantauth/login');
				return false;
			}
		}

		// Validar tenant_id
		if (!$this->session->userdata('tenant_id')) {
			return $this->_api_error(403, 'Acceso no autorizado: tenant no encontrado');
		}

		return true;
	}

	/**
	 * Renderiza una vista dentro del template del panel tenant
	 */
	public function render_template($page = null, $data = [])
	{
		if (!$page || !is_string($page)) {
			show_error('Vista no especificada o inválida.');
		}

		$view_path = APPPATH . 'views/' . $page . '.php';
		if (!file_exists($view_path)) {
			show_404();
		}

		$data = array_merge($this->data, $data);
		$this->load->view('template/header', $data);
		$this->load->view('template/sidebar', $data);
		$this->load->view('template/topbar', $data);
		$this->load->view($page, $data);
		$this->load->view('template/footer', $data);
	}

	/**
	 * Renderiza una vista dentro del template del panel de administración SaaS
	 */
	public function render_admin_template($page = null, $data = [])
	{
		if (!$page || !is_string($page)) {
			show_error('Vista no especificada o inválida.');
		}

		$view_path = APPPATH . 'views/' . $page . '.php';
		if (!file_exists($view_path)) {
			show_404();
		}

		$data = array_merge($this->data, $data);
		$this->load->view('template/header', $data);
		$this->load->view('template/sidebar_admin', $data);
		$this->load->view('template/topbar', $data);
		$this->load->view($page, $data);
		$this->load->view('template/footer', $data);
	}

	/**
	 * Renderiza una vista simple sin layout (útil para páginas públicas o AJAX)
	 */
	public function render_view($page = null, $data = [])
	{
		if (!$page || !is_string($page)) {
			show_error('Vista no especificada o inválida.');
		}

		$view_path = APPPATH . 'views/' . $page . '.php';
		if (!file_exists($view_path)) {
			show_404();
		}

		$data = array_merge($this->data, $data);
		$this->load->view($page, $data);
	}

	/**
	 * Devuelve error en formato API JSON
	 */
	protected function _api_error($code, $message)
	{
		http_response_code($code);
		echo json_encode(['ok' => false, 'msg' => $message]);
		exit;
	}

	/**
	 * Valida si el recurso pertenece al tenant actual
	 */
	protected function _validate_tenant_access($resource_tenant_id)
	{
		$current_tenant = $this->session->userdata('tenant_id');
		if ($resource_tenant_id != $current_tenant) {
			$this->_api_error(403, 'Acceso denegado al recurso solicitado.');
			return false;
		}
		return true;
	}

	/**
	 * Valida si el método actual tiene permiso para renderizar la vista
	 * (Opcional para control granular de vistas)
	 */
	protected function validate_view_access()
	{
		$current_method = $this->router->fetch_method();

		// Aplica solo a métodos terminados en "_view" para separar API de vistas
		if (!empty($this->allowed_views) && substr($current_method, -5) === '_view') {
			if (!in_array($current_method, $this->allowed_views)) {
				$this->_api_error(403, 'Acceso denegado a la vista: ' . $current_method);
			}
		}
	}
}
