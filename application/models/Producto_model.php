<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/TenantScope.php';

class Producto_model extends CI_Model
{
	use TenantScope {
		TenantScope::__construct as private __tenantScopeConstruct;
	}

	private $table = 'productos';

	public function __construct()
	{
		parent::__construct();
		// Llamar al constructor del trait para inicializar el tenant_id
		$this->__tenantScopeConstruct();
	}

	/**
	 * Obtiene todos los productos para el tenant actual.
	 * @param bool $only_active Solo productos activos
	 * @param array $filters Filtros adicionales (ej: ['destacado' => 1])
	 * @return array
	 */
	public function get_all($only_active = false, $filters = [])
	{
		$this->db->from($this->table);

		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		if ($only_active) {
			$this->db->where('activo', 1);
		}

		// Aplicar filtros adicionales
		if (!empty($filters)) {
			$this->db->where($filters);
		}

		$this->db->order_by('orden', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}

	/**
	 * Obtiene un producto por su ID, asegurando que pertenezca al tenant actual.
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
	 * Crea un nuevo producto. El tenant_id se añade automáticamente desde el trait.
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
	 * Actualiza un producto, asegurando que pertenezca al tenant actual.
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
	 * Elimina un producto, asegurando que pertenezca al tenant actual.
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
	 * Cuenta los productos del tenant actual.
	 * @param array $filters Filtros opcionales como ['activo' => 1]
	 * @return int
	 */
	public function count($filters = [])
	{
		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		// Aplicar filtros adicionales
		if (isset($filters['activo'])) {
			$this->db->where('activo', (int)$filters['activo']);
		}

		return $this->db->count_all_results($this->table);
	}

	/**
	 * Alias de count() para compatibilidad
	 * @param array $filters Filtros opcionales como ['activo' => 1]
	 * @return int
	 */
	public function count_by_tenant($filters = [])
	{
		return $this->count($filters);
	}

	/**
	 * Obtiene todos los productos del tenant actual
	 * @return array
	 */
	public function get_by_tenant()
	{
		$this->db->select('productos.*, categorias.nombre AS categoria_nombre');
		$this->db->from($this->table);
		$this->db->join('categorias', 'productos.categoria_id = categorias.id');
		// Evitar ambigüedad de la columna tenant_id especificando la tabla
		$this->db->where('productos.tenant_id', (int)$this->tenant_id);
		$this->db->order_by('orden', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}
}
