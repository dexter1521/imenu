<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PermissionTrait – Manejo centralizado de permisos por usuario
 * 
 * ✔ Lee los permisos desde la tabla `permisos`
 * ✔ Valida si un usuario tiene permiso para una acción
 * ✔ Permite proteger controladores y métodos fácilmente
 * 
 * 📌 Uso en un controlador:
 * 
 * use PermissionTrait;
 * 
 * class Pedidos extends MY_Controller {
 *     use PermissionTrait;
 * 
 *     public function __construct() {
 *         parent::__construct();
 *         $this->require_permission('can_products');
 *     }
 * }
 */

trait PermissionTrait
{
	/**
	 * Cache local de permisos del usuario actual
	 * @var array
	 */
	protected $cached_permissions = [];

	/**
	 * Carga los permisos del usuario actual desde la base de datos
	 */
	protected function load_permissions()
	{
		// Si ya fueron cargados previamente, no volver a consultar
		if (!empty($this->cached_permissions)) return;

		$user_id   = $this->session->userdata('user_id');
		$tenant_id = $this->session->userdata('tenant_id');

		if (!$user_id || !$tenant_id) {
			$this->_permission_error('No hay sesión activa o el usuario no está asociado a un tenant.');
		}

		$query = $this->db->get_where('permisos', [
			'user_id'   => $user_id,
			'tenant_id' => $tenant_id
		]);

		if ($query->num_rows() > 0) {
			$this->cached_permissions = $query->row_array();
		} else {
			// Si no hay registro, inicializamos en blanco
			$this->cached_permissions = [
				'can_products'    => 0,
				'can_categories'  => 0,
				'can_adjustments' => 0,
				'can_view_stats'  => 0
			];
		}
	}

	/**
	 * Verifica si el usuario tiene un permiso específico
	 * @param string $permission Clave del permiso (ej. 'can_products')
	 * @return bool
	 */
	protected function has_permission($permission)
	{
		$this->load_permissions();

		if (array_key_exists($permission, $this->cached_permissions)) {
			return (bool) $this->cached_permissions[$permission];
		}

		// Si no existe la clave, se asume que no tiene permiso
		return false;
	}

	/**
	 * Requiere un permiso específico (lanza error 403 si no lo tiene)
	 * @param string $permission Clave del permiso a validar
	 */
	protected function require_permission($permission)
	{
		if (!$this->has_permission($permission)) {
			$this->_permission_error("No tienes permiso para ejecutar esta acción: <b>{$permission}</b>");
		}
	}

	/**
	 * Permite requerir al menos uno de varios permisos
	 * @param array $permissions Lista de permisos válidos
	 */
	protected function require_any_permission(array $permissions)
	{
		$this->load_permissions();

		foreach ($permissions as $perm) {
			if ($this->has_permission($perm)) return true;
		}

		$this->_permission_error('No tienes ninguno de los permisos requeridos.');
	}

	/**
	 * Manejo de errores de permisos
	 */
	private function _permission_error($message)
	{
		if ($this->input->is_ajax_request()) {
			$this->_api_error(403, $message);
		} else {
			show_error($message, 403, 'Permiso denegado');
		}
		exit;
	}
}
