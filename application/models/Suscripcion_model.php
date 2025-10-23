<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suscripcion_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todas las suscripciones
	 */
	public function get_all()
	{
		$this->db->select('suscripciones.*, tenants.nombre as tenant_nombre, planes.nombre as plan_nombre');
		$this->db->from('suscripciones');
		$this->db->join('tenants', 'tenants.id = suscripciones.tenant_id');
		$this->db->join('planes', 'planes.id = suscripciones.plan_id');
		$data = $this->db->order_by('suscripciones.id', 'DESC')->get()->result();
		return $data;
	}

	/**
	 * Obtener suscripción por ID
	 */
	public function get($id)
	{
		return $this->db->where('id', (int)$id)->get('suscripciones')->row();
	}

	/**
	 * Obtener primer resultado de la consulta chainable
	 */
	public function get_one()
	{
		return $this->db->get('suscripciones')->row();
	}

	/**
	 * Obtener todos los resultados de la consulta chainable
	 */
	public function get_results()
	{
		return $this->db->get('suscripciones')->result();
	}

	/**
	 * Filtrar por campo
	 */
	public function where($field, $value)
	{
		$this->db->where($field, $value);
		return $this;
	}

	/**
	 * Ordenar resultados
	 */
	public function order_by($field, $direction = 'ASC')
	{
		$this->db->order_by($field, $direction);
		return $this;
	}

	/**
	 * Limitar resultados
	 */
	public function limit($limit, $offset = 0)
	{
		$this->db->limit($limit, $offset);
		return $this;
	}

	/**
	 * Obtener todas las suscripciones de un tenant
	 * @param int $tenant_id
	 * @return array
	 */
	public function get_by_tenant($tenant_id)
	{
		return $this->db->where('tenant_id', (int)$tenant_id)
			->order_by('inicio', 'DESC')
			->get('suscripciones')
			->result();
	}

	/**
	 * Obtener suscripción activa de un tenant
	 * @param int $tenant_id
	 * @return object|null
	 */
	public function get_active_by_tenant($tenant_id)
	{
		$now = date('Y-m-d');
		return $this->db->where('tenant_id', (int)$tenant_id)
			->where('estatus', 'activa')
			->where('inicio <=', $now)
			->where('fin >=', $now)
			->order_by('fin', 'DESC')
			->get('suscripciones')
			->row();
	}

	/**
	 * Crear nueva suscripción
	 * @param array $data
	 * @return int|false
	 */
	public function insert($data)
	{
		if ($this->db->insert('suscripciones', $data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	/**
	 * Actualizar suscripción
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, $data)
	{
		return $this->db->where('id', (int)$id)->update('suscripciones', $data);
	}

	/**
	 * Eliminar suscripción
	 * @param int $id
	 * @return bool
	 */
	public function delete($id)
	{
		return $this->db->where('id', (int)$id)->delete('suscripciones');
	}

	/**
	 * Contar suscripciones activas
	 * @return int
	 */
	public function count_active()
	{
		$now = date('Y-m-d');
		return $this->db->where('estatus', 'activa')
			->where('inicio <=', $now)
			->where('fin >=', $now)
			->count_all_results('suscripciones');
	}

	/**
	 * Obtener estadísticas de suscripciones para dashboard
	 * @return array
	 */
	public function get_dashboard_stats()
	{
		$now = date('Y-m-d');

		// Total de suscripciones
		$total = $this->db->count_all('suscripciones');

		// Suscripciones activas
		$activas = $this->count_active();

		// Suscripciones que expiran en los próximos 7 días
		$this->db->where('estatus', 'activa');
		$this->db->where('fin >=', $now);
		$this->db->where('fin <=', date('Y-m-d', strtotime('+7 days')));
		$expirando_pronto = $this->db->count_all_results('suscripciones');

		// Suscripciones expiradas
		$this->db->where('fin <', $now);
		$this->db->where('estatus !=', 'cancelada');
		$expiradas = $this->db->count_all_results('suscripciones');

		// Suscripciones nuevas este mes
		$this->db->where('MONTH(inicio)', date('m'));
		$this->db->where('YEAR(inicio)', date('Y'));
		$nuevas_mes = $this->db->count_all_results('suscripciones');

		return [
			'total' => $total,
			'activas' => $activas,
			'expirando_pronto' => $expirando_pronto,
			'expiradas' => $expiradas,
			'nuevas_mes' => $nuevas_mes
		];
	}
}
