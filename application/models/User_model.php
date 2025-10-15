<?php

defined('BASEPATH') or exit('No direct script access allowed');

// ===== application/models/User_model.php =====
class User_model extends CI_Model
{
	public function list_by_tenant($tenant_id)
	{
		return $this->db->select('u.id,u.nombre,u.email,u.rol,u.activo,p.can_products,p.can_categories,p.can_adjustments,p.can_view_stats')
			->from('users u')
			->join('permisos p', 'p.user_id=u.id AND p.tenant_id=u.tenant_id', 'left')
			->where('u.tenant_id', $tenant_id)
			->order_by('u.id', 'DESC')->get()->result();
	}

	public function create_staff($tenant_id, $nombre, $email, $password_plain)
	{
		$hash = password_hash($password_plain, PASSWORD_DEFAULT);
		$this->db->insert('users', ['tenant_id' => $tenant_id, 'nombre' => $nombre, 'email' => $email, 'password' => $hash, 'rol' => 'staff', 'activo' => 1]);
		return $this->db->insert_id();
	}

	public function update_user($tenant_id, $id, $data)
	{
		return $this->db->update('users', $data, ['id' => $id, 'tenant_id' => $tenant_id]);
	}
	
	public function delete_user($tenant_id, $id)
	{
		$this->db->delete('permisos', ['user_id' => $id, 'tenant_id' => $tenant_id]);
		return $this->db->delete('users', ['id' => $id, 'tenant_id' => $tenant_id]);
	}
}
