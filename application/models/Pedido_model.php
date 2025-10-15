<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pedido_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Crear pedido con items (método mejorado)
	 */
	public function create_with_items($pedido_data, $items)
	{
		$this->db->trans_start();

		// Calcular total
		$total = 0;
		$items_validados = [];

		foreach ($items as $item) {
			// Validar que el producto existe y pertenece al tenant
			$producto = $this->db->get_where('productos', [
				'id' => $item['producto_id'],
				'tenant_id' => $pedido_data['tenant_id'],
				'activo' => 1
			], 1)->row();

			if (!$producto) {
				$this->db->trans_rollback();
				return false;
			}

			$cantidad = (int)$item['cantidad'];
			$precio_unit = (float)$producto->precio;
			$subtotal = $precio_unit * $cantidad;
			$total += $subtotal;

			$items_validados[] = [
				'producto_id' => $producto->id,
				'nombre' => $producto->nombre,
				'precio_unit' => $precio_unit,
				'cantidad' => $cantidad,
				'subtotal' => $subtotal
			];
		}

		// Insertar pedido
		$pedido_data['total'] = $total;
		$this->db->insert('pedidos', $pedido_data);
		$pedido_id = $this->db->insert_id();

		// Insertar items
		foreach ($items_validados as $item) {
			$item['pedido_id'] = $pedido_id;
			$this->db->insert('pedido_items', $item);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return false;
		}

		return $pedido_id;
	}

	/**
	 * Método legacy (mantener compatibilidad)
	 */
	public function create($tenant_id, $nombre, $telefono, $metodo_pago, $items)
	{
		$pedido_data = [
			'tenant_id' => $tenant_id,
			'nombre_cliente' => $nombre,
			'telefono_cliente' => $telefono,
			'metodo_pago' => $metodo_pago,
			'estado' => 'pendiente'
		];

		return $this->create_with_items($pedido_data, $items);
	}

	/**
	 * Listar pedidos por tenant con paginación y filtros
	 */
	public function list_by_tenant($tenant_id, $filters = [])
	{
		$this->db->select('p.*, COUNT(pi.id) as total_items');
		$this->db->from('pedidos p');
		$this->db->join('pedido_items pi', 'pi.pedido_id = p.id', 'left');
		$this->db->where('p.tenant_id', $tenant_id);

		// Aplicar filtros
		if (!empty($filters['estado'])) {
			$this->db->where('p.estado', $filters['estado']);
		}

		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('p.creado_en >=', $filters['fecha_inicio']);
		}

		if (!empty($filters['fecha_fin'])) {
			$this->db->where('p.creado_en <=', $filters['fecha_fin']);
		}

		if (!empty($filters['cliente'])) {
			$this->db->like('p.nombre_cliente', $filters['cliente']);
		}

		if (!empty($filters['metodo_pago'])) {
			$this->db->where('p.metodo_pago', $filters['metodo_pago']);
		}

		// Filtro por rango de total
		if (!empty($filters['total_min'])) {
			$this->db->where('p.total >=', (float)$filters['total_min']);
		}

		if (!empty($filters['total_max'])) {
			$this->db->where('p.total <=', (float)$filters['total_max']);
		}

		$this->db->group_by('p.id');
		
		// Ordenamiento
		$orden_campo = 'p.creado_en';
		$orden_direccion = !empty($filters['orden']) && $filters['orden'] === 'asc' ? 'ASC' : 'DESC';
		
		if (!empty($filters['order_by'])) {
			$campos_validos = ['creado_en', 'total', 'estado', 'nombre_cliente'];
			if (in_array($filters['order_by'], $campos_validos)) {
				$orden_campo = 'p.' . $filters['order_by'];
			}
		}
		
		$this->db->order_by($orden_campo, $orden_direccion);

		// Paginación
		if (!empty($filters['limit'])) {
			$this->db->limit($filters['limit'], $filters['offset'] ?? 0);
		}

		return $this->db->get()->result();
	}

	/**
	 * Obtener pedido con items
	 */
	public function get_with_items($tenant_id, $pedido_id)
	{
		$pedido = $this->db->get_where('pedidos', [
			'tenant_id' => $tenant_id, 
			'id' => $pedido_id
		], 1)->row();

		if (!$pedido) {
			return null;
		}

		// Obtener items del pedido
		$this->db->select('pi.*, p.nombre as producto_nombre');
		$this->db->from('pedido_items pi');
		$this->db->join('productos p', 'p.id = pi.producto_id', 'left');
		$this->db->where('pi.pedido_id', $pedido_id);
		$pedido->items = $this->db->get()->result();

		return $pedido;
	}

	/**
	 * Actualizar estado del pedido
	 */
	public function update_estado($tenant_id, $pedido_id, $estado)
	{
		$this->db->where([
			'tenant_id' => $tenant_id,
			'id' => $pedido_id
		]);
		
		$updated = $this->db->update('pedidos', ['estado' => $estado]);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Eliminar pedido y sus items
	 */
	public function delete_pedido($tenant_id, $pedido_id)
	{
		$this->db->trans_start();

		// Verificar que el pedido pertenece al tenant
		$pedido = $this->db->get_where('pedidos', [
			'tenant_id' => $tenant_id,
			'id' => $pedido_id
		], 1)->row();

		if (!$pedido) {
			$this->db->trans_rollback();
			return false;
		}

		// Eliminar items
		$this->db->delete('pedido_items', ['pedido_id' => $pedido_id]);
		
		// Eliminar pedido
		$this->db->delete('pedidos', ['id' => $pedido_id]);

		$this->db->trans_complete();
		return $this->db->trans_status() !== FALSE;
	}

	/**
	 * Contar pedidos por tenant con filtros
	 */
	public function count_by_tenant($tenant_id, $filters = [])
	{
		$this->db->where('tenant_id', $tenant_id);

		// Aplicar los mismos filtros que en list_by_tenant
		if (!empty($filters['estado'])) {
			$this->db->where('estado', $filters['estado']);
		}

		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('creado_en >=', $filters['fecha_inicio']);
		}

		if (!empty($filters['fecha_fin'])) {
			$this->db->where('creado_en <=', $filters['fecha_fin']);
		}

		if (!empty($filters['cliente'])) {
			$this->db->like('nombre_cliente', $filters['cliente']);
		}

		if (!empty($filters['metodo_pago'])) {
			$this->db->where('metodo_pago', $filters['metodo_pago']);
		}

		return $this->db->count_all_results('pedidos');
	}

	/**
	 * Obtener estadísticas de pedidos
	 */
	public function get_stats($tenant_id, $fecha_inicio = null, $fecha_fin = null)
	{
		$this->db->where('tenant_id', $tenant_id);
		
		if ($fecha_inicio) {
			$this->db->where('creado_en >=', $fecha_inicio);
		}
		
		if ($fecha_fin) {
			$this->db->where('creado_en <=', $fecha_fin);
		}

		// Total de pedidos y ventas
		$this->db->select('COUNT(id) as total_pedidos, SUM(total) as total_ventas, estado');
		$this->db->group_by('estado');
		$stats_por_estado = $this->db->get('pedidos')->result();

		return [
			'por_estado' => $stats_por_estado,
			'total_pedidos' => array_sum(array_column($stats_por_estado, 'total_pedidos')),
			'total_ventas' => array_sum(array_column($stats_por_estado, 'total_ventas'))
		];
	}
}
