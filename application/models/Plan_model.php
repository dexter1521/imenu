<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Plan_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todos los planes
	 */
	public function get_all()
	{
		return $this->db->order_by('precio_mensual', 'ASC')->get('planes')->result();
	}

	/**
	 * Obtener un plan por ID
	 */
	public function get($id)
	{
		return $this->db->where('id', (int)$id)->get('planes')->row();
	}

	/**
	 * Insertar nuevo plan
	 */
	public function insert($data)
	{
		$this->db->insert('planes', $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar plan
	 */
	public function update($id, $data)
	{
		$this->db->where('id', (int)$id);
		return $this->db->update('planes', $data);
	}

	/**
	 * Eliminar plan
	 */
	public function delete($id)
	{
		$this->db->where('id', (int)$id);
		return $this->db->delete('planes');
	}
}
