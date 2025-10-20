<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class MY_Controller
 * Controlador base centralizado para iMenu
 *
 * - Proporciona métodos auxiliares para renderizado de vistas.
 * - Centraliza datos comunes para todas las vistas.
 * - NO valida autenticación (delegada a AuthHook).
 * - Asume que AuthHook ya validó el JWT y lo dejó disponible en $CI->jwt.
 */

/**
 * Class MY_Controller
 *
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_URI $uri
 * @property CI_Router $router
 * @property object $jwt Payload del JWT decodificado (disponible después de AuthHook)
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

		// Inicializar datos comunes para todas las vistas
		$this->_init_common_data();
	}

	/**
	 * Inicializa datos comunes disponibles en todas las vistas
	 * Asume que AuthHook ya validó el JWT y está disponible en $this->jwt
	 */
	protected function _init_common_data()
	{
		$this->data['page_title'] = 'iMenu';

		// Si AuthHook ya validó y dejó el JWT disponible, extraer datos del usuario
		if (isset($this->jwt) && is_object($this->jwt)) {
			$this->data['user_name'] = $this->jwt->nombre ?? 'Usuario';
			$this->data['user_role'] = $this->jwt->rol ?? null;
			$this->data['tenant_id'] = $this->jwt->tenant_id ?? null;
			$this->data['user_id'] = $this->jwt->sub ?? null;
		} else {
			// Fallback para rutas públicas que no requieren auth
			$this->data['user_name'] = 'Invitado';
			$this->data['user_role'] = null;
			$this->data['tenant_id'] = null;
			$this->data['user_id'] = null;
		}
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

		// Agregar URL de logout para admin
		$data['logout_url'] = site_url('adminauth/logout');

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
	 * Devuelve respuesta de éxito en formato JSON
	 * @param mixed $data Datos a retornar
	 * @param string $message Mensaje opcional
	 */
	protected function _api_success($data = null, $message = 'OK')
	{
		$this->output
			->set_status_header(200)
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'message' => $message,
				'data' => $data
			]));
	}

	/**
	 * Devuelve error en formato API JSON
	 * @param int $code Código HTTP
	 * @param string $message Mensaje de error
	 */
	protected function _api_error($code, $message)
	{
		$this->output
			->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => false,
				'message' => $message
			]));
	}

	/**
	 * Valida si el recurso pertenece al tenant actual
	 * @param int $resource_tenant_id ID del tenant dueño del recurso
	 * @return bool
	 */
	protected function _validate_tenant_access($resource_tenant_id)
	{
		// Obtener el tenant_id actual del JWT (ya validado por AuthHook)
		$current_tenant = isset($this->jwt->tenant_id) ? (int)$this->jwt->tenant_id : 0;

		// Los administradores SaaS pueden acceder a todos los recursos
		if (isset($this->jwt->rol) && $this->jwt->rol === 'admin') {
			return true;
		}

		if ((int)$resource_tenant_id !== $current_tenant) {
			$this->_api_error(403, 'Acceso denegado al recurso solicitado.');
			return false;
		}

		return true;
	}

	/**
	 * Obtiene el ID del usuario actual desde el JWT
	 * @return int
	 */
	protected function _current_user_id()
	{
		return isset($this->jwt->sub) ? (int)$this->jwt->sub : 0;
	}

	/**
	 * Obtiene el ID del tenant actual desde el JWT
	 * @return int
	 */
	protected function _current_tenant_id()
	{
		return isset($this->jwt->tenant_id) ? (int)$this->jwt->tenant_id : 0;
	}

	/**
	 * Obtiene el rol actual desde el JWT
	 * @return string|null
	 */
	protected function _current_role()
	{
		return isset($this->jwt->rol) ? $this->jwt->rol : null;
	}
}
