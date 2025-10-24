<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/TenantScope.php';

class Categoria_model extends CI_Model
{
	use TenantScope {
		TenantScope::__construct as private __tenantScopeConstruct;
	}

	private $table = 'categorias';

	public function __construct()
	{
		parent::__construct();
		// Llamar al constructor del trait para inicializar el tenant_id
		$this->__tenantScopeConstruct();
	}

	/**
	 * Obtiene todas las categorías para el tenant actual, ordenadas.
	 * @param bool $only_active Solo categorías activas
	 * @return array
	 */
	public function get_all($only_active = false)
	{
		$this->db->from($this->table);

		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		if ($only_active) {
			$this->db->where('activo', 1);
		}

		$this->db->order_by('orden', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}

	/**
	 * Obtiene una categoría por su ID, asegurando que pertenezca al tenant actual.
	 * @param int $id
	 * @return object|null
	 */
	public function get_by_id($id)
	{
		$this->db->from($this->table);
		$this->db->where('id', (int)$id);

		// Aplicar el scope del tenant actual para seguridad
		$this->applyTenantScope($this->db);

		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Crea una nueva categoría. El tenant_id se añade automáticamente desde el trait.
	 * @param array $data
	 * @return int|false
	 */
	public function create($data)
	{
		// Asegurarse de que el tenant_id del scope se incluya en la inserción
		if ($this->tenant_id && !isset($data['tenant_id'])) {
			$data['tenant_id'] = $this->tenant_id;
		}

		if ($this->db->insert($this->table, $data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	/**
	 * Actualiza una categoría, asegurando que pertenezca al tenant actual.
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, $data)
	{
		$this->db->where('id', (int)$id);

		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		return $this->db->update($this->table, $data);
	}

	/**
	 * Elimina una categoría, asegurando que pertenezca al tenant actual.
	 * @param int $id
	 * @return bool
	 */
	public function delete($id)
	{
		$this->db->where('id', (int)$id);

		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		return $this->db->delete($this->table);
	}

	/**
	 * Cuenta las categorías del tenant actual.
	 * @return int
	 */
	public function count()
	{
		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);
		return $this->db->count_all_results($this->table);
	}

	/**
	 * Alias de count() para compatibilidad
	 * @return int
	 */
	public function count_by_tenant()
	{
		return $this->count();
	}

	/**
	 * Obtiene todas las categorías del tenant actual
	 * @return array
	 */
	public function get_by_tenant()
	{
		$this->db->from($this->table);
		$this->applyTenantScope($this->db);
		$this->db->order_by('orden', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}
}
