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

	/**
	 * Obtener planes más usados (con conteo de tenants)
	 * @param int $limit
	 * @return array
	 */
	public function get_most_popular($limit = 5)
	{
		$this->db->select('planes.*, COUNT(tenants.id) as tenant_count');
		$this->db->from('planes');
		$this->db->join('tenants', 'tenants.plan_id = planes.id', 'left');
		$this->db->group_by('planes.id');
		$this->db->order_by('tenant_count', 'DESC');
		$this->db->order_by('planes.precio_mensual', 'DESC');
		$this->db->limit($limit);
		return $this->db->get()->result();
	}

	/**
	 * Obtener estadísticas de planes para dashboard
	 * @return array
	 */
	public function get_dashboard_stats()
	{
		// Total de planes
		$total = $this->db->count_all('planes');

		// Plan más caro
		$this->db->select('nombre, precio_mensual');
		$this->db->order_by('precio_mensual', 'DESC');
		$this->db->limit(1);
		$plan_premium = $this->db->get('planes')->row();

		// Plan más barato
		$this->db->select('nombre, precio_mensual');
		$this->db->order_by('precio_mensual', 'ASC');
		$this->db->limit(1);
		$plan_basico = $this->db->get('planes')->row();

		return [
			'total_planes' => $total,
			'plan_premium' => $plan_premium,
			'plan_basico' => $plan_basico
		];
	}
}
