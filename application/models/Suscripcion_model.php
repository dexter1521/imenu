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
		return $this->db->get('suscripciones')->result();
	}

	/**
	 * Obtener suscripción por ID
	 */
	public function get($id)
	{
		return $this->db->where('id', (int)$id)->get('suscripciones')->row();
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
}
