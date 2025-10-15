<?php
class Pedido_model extends CI_Model
{
	public function create($tenant_id, $nombre, $tel, $metodo, $items)
	{
		$this->db->trans_start();
		$total = 0.0;

		// Revalidar precios en DB (seguridad)
		$productosCache = [];
		foreach ($items as $it) {
			$pid = (int)$it['producto_id'];
			$qty = max(1, (int)$it['cantidad']);
			if (!isset($productosCache[$pid])) {
				$row = $this->db->get_where('productos', [
					'id' => $pid,
					'tenant_id' => $tenant_id,
					'activo' => 1
				], 1)->row();
				if (!$row) throw new Exception('Producto inválido: ' . $pid);
				$productosCache[$pid] = $row;
			}
			$precio = (float)$productosCache[$pid]->precio;
			$total += ($precio * $qty);
		}

		$this->db->insert('pedidos', [
			'tenant_id' => $tenant_id,
			'nombre_cliente' => $nombre,
			'telefono_cliente' => $tel,
			'metodo_pago' => $metodo,
			'total' => $total,
			'estado' => 'pendiente'
		]);
		$pedido_id = $this->db->insert_id();

		foreach ($items as $it) {
			$pid = (int)$it['producto_id'];
			$qty = max(1, (int)$it['cantidad']);
			$p = $productosCache[$pid];
			$precio = (float)$p->precio;
			$sub = $precio * $qty;
			$this->db->insert('pedido_items', [
				'pedido_id' => $pedido_id,
				'producto_id' => $pid,
				'nombre' => $p->nombre,
				'precio_unit' => $precio,
				'cantidad' => $qty,
				'subtotal' => $sub,
			]);
		}

		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) throw new Exception('No se pudo crear el pedido');
		return $pedido_id;
	}

	public function list_by_tenant($tenant_id)
	{
		return $this->db->select('id,nombre_cliente,telefono_cliente,metodo_pago,total,estado,creado_en')
			->from('pedidos')
			->where('tenant_id', $tenant_id)
			->order_by('id', 'DESC')->get()->result();
	}

	public function get_with_items($tenant_id, $pedido_id)
	{
		$pedido = $this->db->get_where('pedidos', [
			'id' => $pedido_id,
			'tenant_id' => $tenant_id
		], 1)->row();
		if (!$pedido) return null;
		$items = $this->db->get_where('pedido_items', [
			'pedido_id' => $pedido_id
		])->result();
		return (object)['pedido' => $pedido, 'items' => $items];
	}
}
