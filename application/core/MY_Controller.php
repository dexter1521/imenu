<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class MY_Controller
 *
 * @property CI_Session $session
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Output $output
 */

/**
 * Controlador base personalizado para iMenu
 */
class MY_Controller extends CI_Controller
{
	/**
	 * Permisos del usuario autenticado
	 * @var array
	 */



	/**
	 * Instancia principal de CodeIgniter
	 * @var CI_Controller
	 */
	protected $CI;

	/**
	 * Permisos del usuario autenticado
	 * @var array
	 */
	protected $permission = [];

	/**
	 * Datos globales para las vistas
	 * @var array
	 */
	protected $data = [];

	// Centralizar validación de vistas permitidas
	protected $allowed_views = [];

	public function __construct()
	{
		parent::__construct();

		// Excluir métodos específicos de la validación de autenticación
		$excluded_methods = ['login'];
		$current_method = $this->router->fetch_method();
		if (!in_array($current_method, $excluded_methods)) {
			if (!$this->_verify_auth()) {
				exit;
			}
		}

		// Datos comunes para todas las vistas
		$this->data['page_title'] = 'iMenu';
		$this->data['user_name'] = 'Usuario'; // Se puede cargar desde sesión
	}

	/**
	 * Renderiza una vista con la plantilla base
	 * @param string $page Vista a renderizar (sin .php)
	 * @param array $data Datos para la vista
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
	 * Renderiza una vista con la plantilla de admin
	 * @param string $page Vista a renderizar (sin .php)
	 * @param array $data Datos para la vista
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
	 * Renderiza una vista simple sin plantilla
	 * @param string $page Vista a renderizar (sin .php)
	 * @param array $data Datos para la vista
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
	 * Verificar autenticación del usuario
	 */
	protected function _verify_auth()
	{
		// Intentar poblar la información del JWT desde header/cookie
		if (function_exists('jwt_require')) {
			// jwt_require() enviará respuesta y terminará la ejecución si el token falta o es inválido
			jwt_require();
		}

		$tenant_id = current_tenant_id();

		if (!$tenant_id) {
			$this->_api_error(401, 'Acceso no autorizado');
			return false;
		}

		return true;
	}

	/**
	 * Manejar errores de API
	 */
	protected function _api_error($code, $message)
	{
		http_response_code($code);
		echo json_encode(['ok' => false, 'msg' => $message]);
		exit;
	}

	/**
	 * Validar acceso al tenant
	 */
	protected function _validate_tenant_access($resource_tenant_id)
	{
		$current_tenant = current_tenant_id();

		if ($resource_tenant_id != $current_tenant) {
			$this->_api_error(403, 'Acceso denegado');
			return false;
		}

		return true;
	}

	/**
	 * Validar vistas permitidas para el controlador
	 */
	protected function validate_view_access()
	{
		$current_method = $this->router->fetch_method();
		// Solo aplicar la validación para métodos que representan vistas (terminan en _view).
		// Esto evita bloquear endpoints API (como tenants, planes, pagos) que deben estar accesibles vía AJAX/API.
		if (!empty($this->allowed_views) && substr($current_method, -5) === '_view') {
			if (!in_array($current_method, $this->allowed_views)) {
				$this->_api_error(403, 'Acceso denegado a la vista: ' . $current_method);
			}
		}
	}
}
