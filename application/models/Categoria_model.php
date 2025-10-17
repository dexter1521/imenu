<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Categoria_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todas las categorías de un tenant ordenadas
	 * @param int $tenant_id
	 * @param bool $only_active Solo categorías activas
	 * @return array
	 */
	public function get_by_tenant($tenant_id, $only_active = false)
	{
		$this->db->where('tenant_id', (int)$tenant_id);
		if ($only_active) {
			$this->db->where('activo', 1);
		}
		$this->db->order_by('orden', 'ASC');
		return $this->db->get('categorias')->result();
	}

	/**
	 * Obtener una categoría por ID
	 * @param int $id
	 * @param int $tenant_id
	 * @return object|null
	 */
	public function get($id, $tenant_id = null)
	{
		$this->db->where('id', (int)$id);
		if ($tenant_id !== null) {
			$this->db->where('tenant_id', (int)$tenant_id);
		}
		return $this->db->get('categorias')->row();
	}

	/**
	 * Crear nueva categoría
	 * @param array $data
	 * @return int ID de la categoría creada
	 */
	public function create($data)
	{
		$this->db->insert('categorias', $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar categoría
	 * @param int $id
	 * @param int $tenant_id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, $tenant_id, $data)
	{
		$this->db->where('id', (int)$id);
		$this->db->where('tenant_id', (int)$tenant_id);
		return $this->db->update('categorias', $data);
	}

	/**
	 * Eliminar categoría
	 * @param int $id
	 * @param int $tenant_id
	 * @return bool
	 */
	public function delete($id, $tenant_id)
	{
		$this->db->where('id', (int)$id);
		$this->db->where('tenant_id', (int)$tenant_id);
		return $this->db->delete('categorias');
	}

	/**
	 * Contar categorías de un tenant
	 * @param int $tenant_id
	 * @return int
	 */
	public function count_by_tenant($tenant_id)
	{
		return $this->db->where('tenant_id', (int)$tenant_id)
			->count_all_results('categorias');
	}
}
