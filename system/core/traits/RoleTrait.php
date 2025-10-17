<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * RoleTrait – Control de acceso por rol (admin, owner, staff)
 *
 * 📌 Uso:
 * use RoleTrait;
 * 
 * class Productos extends MY_Controller {
 *     use RoleTrait;
 * 
 *     public function __construct() {
 *         parent::__construct();
 *         $this->require_role(['owner', 'admin']); // solo estos roles entran
 *     }
 * }
 */

trait RoleTrait
{
	/**
	 * Obtiene el rol actual del usuario desde la sesión o JWT
	 */
	protected function current_role()
	{
		// 🔎 Prioridad: JWT → Sesión
		if (function_exists('current_role') && current_role()) {
			return current_role(); // desde helper JWT
		}

		if ($this->session && $this->session->userdata('rol')) {
			return $this->session->userdata('rol');
		}

		return null;
	}

	/**
	 * Requiere uno o varios roles para continuar
	 * @param string|array $roles Rol o lista de roles permitidos
	 */
	protected function require_role($roles)
	{
		$rol_actual = $this->current_role();
		$roles = is_array($roles) ? $roles : [$roles];

		if (!$rol_actual || !in_array($rol_actual, $roles)) {
			$this->_role_error('Acceso denegado: este recurso requiere rol: ' . implode(', ', $roles));
		}
	}

	/**
	 * Requiere que el rol sea exactamente uno
	 */
	protected function require_exact_role($role)
	{
		$rol_actual = $this->current_role();
		if ($rol_actual !== $role) {
			$this->_role_error("Acceso denegado: se requiere rol {$role}");
		}
	}

	/**
	 * Lanza error 403 en caso de rol inválido
	 */
	private function _role_error($message)
	{
		if ($this->input->is_ajax_request()) {
			$this->_api_error(403, $message);
		} else {
			show_error($message, 403, 'Rol no autorizado');
		}
		exit;
	}
}
