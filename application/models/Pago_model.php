<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pago_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todos los pagos con filtros opcionales
	 */
	public function get_all()
	{
		return $this->db->get('pagos')->result();
	}

	/**
	 * Obtener pagos por tenant_id
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
}
