<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pedido_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function create($tenant_id, $nombre, $telefono, $metodo_pago, $items)
	{
		$this->db->trans_start();

		$pedido_data = [
			'tenant_id' => $tenant_id,
			'nombre' => $nombre,
			'telefono' => $telefono,
			'metodo_pago' => $metodo_pago,
			'fecha' => date('Y-m-d H:i:s')
		];
		$this->db->insert('pedidos', $pedido_data);
		$pedido_id = $this->db->insert_id();

		foreach ($items as $item) {
			$item_data = [
				'pedido_id' => $pedido_id,
				'producto_id' => $item['producto_id'],
				'cantidad' => $item['cantidad']
			];
			$this->db->insert('pedido_items', $item_data);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			throw new Exception('Error al crear el pedido');
		}

		return $pedido_id;
	}

	public function list_by_tenant($tenant_id)
	{
		return $this->db->get_where('pedidos', ['tenant_id' => $tenant_id])->result();
	}

	public function get_with_items($tenant_id, $pedido_id)
	{
		$pedido = $this->db->get_where('pedidos', ['tenant_id' => $tenant_id, 'id' => $pedido_id], 1)->row();
		if (!$pedido) {
			return null;
		}

		$pedido->items = $this->db->get_where('pedido_items', ['pedido_id' => $pedido_id])->result();
		return $pedido;
	}
}
