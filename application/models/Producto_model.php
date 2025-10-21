<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Producto_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todos los productos de un tenant ordenados
	 * @param int $tenant_id
	 * @param bool $only_active Solo productos activos
	 * @return array
	 */
	public function get_by_tenant($tenant_id, $only_active = false)
	{
		$this->db->where('tenant_id', (int)$tenant_id);
		if ($only_active) {
			$this->db->where('activo', 1);
		}
		$this->db->order_by('orden', 'ASC');
		return $this->db->get('productos')->result();
	}

	/**
	 * Obtener un producto por ID
	 * @param int $id
	 * @param int $tenant_id (opcional)
	 * @return object|null
	 */
	public function get($id, $tenant_id = null)
	{
		$this->db->where('id', (int)$id);
		if ($tenant_id !== null) {
			$this->db->where('tenant_id', (int)$tenant_id);
		}
		return $this->db->get('productos')->row();
	}

	/**
	 * Crear nuevo producto
	 * @param array $data
	 * @return int ID del producto creado
	 */
	public function create($data)
	{
		$this->db->insert('productos', $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar producto
	 * @param int $id
	 * @param int $tenant_id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, $tenant_id, $data)
	{
		$this->db->where('id', (int)$id);
		$this->db->where('tenant_id', (int)$tenant_id);
		return $this->db->update('productos', $data);
	}

	/**
	 * Eliminar producto
	 * @param int $id
	 * @param int $tenant_id
	 * @return bool
	 */
	public function delete($id, $tenant_id)
	{
		$this->db->where('id', (int)$id);
		$this->db->where('tenant_id', (int)$tenant_id);
		return $this->db->delete('productos');
	}

	/**
	 * Contar productos de un tenant
	 * @param int $tenant_id
	 * @param array $filters Filtros opcionales como ['activo' => 1]
	 * @return int
	 */
	public function count_by_tenant($tenant_id, $filters = [])
	{
		$this->db->where('tenant_id', (int)$tenant_id);

		// Aplicar filtros adicionales
		if (isset($filters['activo'])) {
			$this->db->where('activo', (int)$filters['activo']);
		}

		return $this->db->count_all_results('productos');
	}
}
