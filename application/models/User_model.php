<?php defined('BASEPATH') or exit('No direct script access allowed');

// ===== application/models/User_model.php =====
class User_model extends CI_Model
{
	/**
	 * Buscar usuario por email activo
	 * @param string $email
	 * @return object|null
	 */
	public function get_by_email($email)
	{
		$query = $this->db->get_where('users', ['email' => $email, 'activo' => 1], 1);
		return $query->row();
	}

	/**
	 * Buscar usuario por ID
	 * @param int $user_id
	 * @return object|null
	 */
	public function get($user_id)
	{
		$query = $this->db->get_where('users', ['id' => $user_id], 1);
		return $query->row();
	}

	/**
	 * Verificar contraseña
	 * @param string $plain_password
	 * @param string $hashed_password
	 * @return bool
	 */
	public function verify_password($plain_password, $hashed_password)
	{
		return password_verify($plain_password, $hashed_password);
	}

	/**
	 * Listar usuarios por tenant
	 * @param int $tenant_id
	 * @return array
	 */
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
