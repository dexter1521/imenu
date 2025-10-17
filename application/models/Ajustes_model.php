<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ajustes_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener ajustes de un tenant
	 * @param int $tenant_id
	 * @return object|null
	 */
	public function get_by_tenant($tenant_id)
	{
		return $this->db->where('tenant_id', (int)$tenant_id)
			->get('ajustes')
			->row();
	}

	/**
	 * Crear ajustes por defecto para un tenant nuevo
	 * @param int $tenant_id
	 * @return int ID de ajustes creado
	 */
	public function create_default($tenant_id)
	{
		$defaults = [
			'tenant_id' => (int)$tenant_id,
			'idioma' => 'es',
			'moneda' => 'MXN',
			'formato_precio' => '$0.00',
			'show_precios' => 1,
			'show_imgs' => 1
		];
		$this->db->insert('ajustes', $defaults);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar o insertar ajustes (upsert)
	 * @param int $tenant_id
	 * @param array $data
	 * @return bool
	 */
	public function upsert($tenant_id, $data)
	{
		$exists = $this->get_by_tenant($tenant_id);

		if ($exists) {
			// Actualizar existente
			$this->db->where('tenant_id', (int)$tenant_id);
			return $this->db->update('ajustes', $data);
		} else {
			// Insertar nuevo
			$data['tenant_id'] = (int)$tenant_id;
			$this->db->insert('ajustes', $data);
			return $this->db->insert_id() > 0;
		}
	}
}
