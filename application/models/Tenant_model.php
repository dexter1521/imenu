<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tenant_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todos los tenants
	 */
	public function get_all()
	{
		return $this->db->order_by('created_at', 'DESC')->get('tenants')->result();
	}

	/**
	 * Obtener un tenant por ID
	 */
	public function get($id)
	{
		return $this->db->where('id', (int)$id)->get('tenants')->row();
	}

	/**
	 * Obtener tenant por slug
	 */
	public function get_by_slug($slug)
	{
		return $this->db->where('slug', $slug)->get('tenants')->row();
	}

	/**
	 * Verificar si un slug es único (excluyendo un ID específico)
	 */
	public function is_slug_unique($slug, $exclude_id = null)
	{
		$this->db->where('slug', $slug);
		if ($exclude_id) {
			$this->db->where('id !=', (int)$exclude_id);
		}
		$count = $this->db->count_all_results('tenants');
		return $count === 0;
	}

	/**
	 * Insertar nuevo tenant
	 */
	public function insert($data)
	{
		$this->db->insert('tenants', $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar tenant
	 */
	public function update($id, $data)
	{
		$this->db->where('id', (int)$id);
		return $this->db->update('tenants', $data);
	}

	/**
	 * Eliminar tenant y todos sus datos relacionados (cascada)
	 */
	public function delete_cascade($id)
	{
		$id = (int)$id;

		// Iniciar transacción
		$this->db->trans_start();

		// Obtener IDs de pedidos para eliminar items
		$pedidos = $this->db->select('id')->where('tenant_id', $id)->get('pedidos')->result();
		$pedido_ids = array_column($pedidos, 'id');

		// Eliminar en orden de dependencias
		if (!empty($pedido_ids)) {
			$this->db->where_in('pedido_id', $pedido_ids)->delete('pedido_items');
		}

		$this->db->delete('pedidos', ['tenant_id' => $id]);
		$this->db->delete('productos', ['tenant_id' => $id]);
		$this->db->delete('categorias', ['tenant_id' => $id]);
		$this->db->delete('ajustes', ['tenant_id' => $id]);
		$this->db->delete('permisos', ['tenant_id' => $id]);
		$this->db->delete('suscripciones', ['tenant_id' => $id]);
		$this->db->delete('pagos', ['tenant_id' => $id]);
		$this->db->delete('users', ['tenant_id' => $id]);
		$this->db->delete('tenants', ['id' => $id]);

		// Finalizar transacción
		$this->db->trans_complete();

		// Log de errores si falla
		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Error al eliminar tenant (ID: ' . $id . '). Error DB: ' . $this->db->error()['message']);
		}

		return $this->db->trans_status();
	}

	/**
	 * Obtener tenant por slug solo si está activo
	 * @param string $slug
	 * @return object|null
	 */
	public function get_by_slug_active($slug)
	{
		return $this->db->where('slug', $slug)
			->where('activo', 1)
			->get('tenants')
			->row();
	}

	/**
	 * Obtener tenant con información del plan
	 * @param int $id
	 * @return object|null
	 */
	public function get_with_plan($id)
	{
		$this->db->select('tenants.*, planes.nombre as plan_nombre, planes.precio_mensual, planes.limite_categorias, planes.limite_items');
		$this->db->from('tenants');
		$this->db->join('planes', 'planes.id = tenants.plan_id', 'left');
		$this->db->where('tenants.id', (int)$id);
		return $this->db->get()->row();
	}

	/**
	 * Actualizar configuración de notificaciones de un tenant
	 * @param int $id
	 * @param array $data Debe contener: notif_email, notif_webhook, notif_whatsapp
	 * @return bool
	 */
	public function update_notification_config($id, $data)
	{
		$allowed_fields = ['notif_email', 'notif_webhook', 'notif_whatsapp'];
		$update_data = [];

		foreach ($allowed_fields as $field) {
			if (isset($data[$field])) {
				$update_data[$field] = $data[$field];
			}
		}

		if (empty($update_data)) {
			return false;
		}

		$this->db->where('id', (int)$id);
		return $this->db->update('tenants', $update_data);
	}

	/**
	 * Obtener configuración de notificaciones de un tenant
	 * @param int $id
	 * @return object|null
	 */
	public function get_notification_config($id)
	{
		$this->db->select('notif_email, notif_webhook, notif_whatsapp');
		$this->db->where('id', (int)$id);
		return $this->db->get('tenants')->row();
	}
}
