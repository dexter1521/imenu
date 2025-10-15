<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Permission_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_by_user($user_id, $tenant_id)
	{
		return $this->db->get_where('permisos', ['user_id' => $user_id, 'tenant_id' => $tenant_id], 1)->row();
	}

	public function upsert($user_id, $tenant_id, $permissions)
	{
		$exists = $this->db->get_where('permisos', ['user_id' => $user_id, 'tenant_id' => $tenant_id], 1)->row();
		if ($exists) {
			return $this->db->update('permisos', $permissions, ['user_id' => $user_id, 'tenant_id' => $tenant_id]);
		} else {
			$permissions['user_id'] = $user_id;
			$permissions['tenant_id'] = $tenant_id;
			return $this->db->insert('permisos', $permissions);
		}
	}
}
