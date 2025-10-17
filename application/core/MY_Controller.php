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
		
		// Si hay JWT válido, extraer datos del usuario
		if (isset($this->jwt)) {
			$this->data['user_name'] = $this->jwt->nombre ?? 'Usuario';
			$this->data['user_role'] = $this->jwt->rol ?? null;
			$this->data['tenant_id'] = $this->jwt->tenant_id ?? null;
		} else {
			$this->data['user_name'] = 'Usuario';
			$this->data['user_role'] = null;
			$this->data['tenant_id'] = null;
		}
	}

	/**
	 * Verifica que el usuario esté autenticado mediante JWT
	 */
	protected function _verify_auth()
	{
		// Verificar si existe JWT válido
		if (!is_authenticated()) {
			if ($this->input->is_ajax_request()) {
				return $this->_api_error(401, 'Sesión no válida o expirada');
			} else {
				// Redirección inteligente según el tipo de panel
				$class = $this->router->fetch_class();
				if ($class === 'admin') {
					// Si es el panel de admin SaaS, redirigir a su login
					redirect('/adminpanel/login?expired=1');
				} else {
					// Para el resto (panel de tenant), redirigir al login de la app
					redirect('/app/login?expired=1');
				}
				return false;
			}
		}

		// Decodificar el JWT y almacenar en $this->jwt para acceso en el controlador
		$payload = jwt_decode_from_cookie();
		if (!$payload) {
			if ($this->input->is_ajax_request()) {
				return $this->_api_error(401, 'Token inválido');
			} else {
				$class = $this->router->fetch_class();
				if ($class === 'admin') {
					redirect('/adminpanel/login?expired=1');
				} else {
					redirect('/app/login?expired=1');
				}
				return false;
			}
		}

		// Almacenar el payload en $this->jwt para que esté disponible en los controladores
		$this->jwt = (object)$payload;

		// Validar tenant_id (excepto para rol admin que puede no tener tenant específico)
		if (!isset($payload['tenant_id']) && (!isset($payload['rol']) || $payload['rol'] !== 'admin')) {
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
		// Obtener el tenant_id actual del JWT
		$current_tenant = isset($this->jwt->tenant_id) ? (int)$this->jwt->tenant_id : 0;
		
		// Los administradores SaaS pueden acceder a todos los recursos
		if (isset($this->jwt->rol) && $this->jwt->rol === 'admin') {
			return true;
		}
		
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
