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

	public function __construct()
	{
		parent::__construct();

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
}
