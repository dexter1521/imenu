<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/TenantScope.php';

class Pedido_model extends CI_Model
{
	use TenantScope {
		TenantScope::__construct as private __tenantScopeConstruct;
	}

	public function __construct()
	{
		parent::__construct();
		// Llamar al constructor del trait para inicializar el tenant_id
		$this->__tenantScopeConstruct();
	}

	/**
	 * Crear pedido con items (método mejorado)
	 */
	public function create_with_items($pedido_data, $items)
	{
		$this->db->trans_start();

		// Asegurarse de que el tenant_id del scope se incluya en la inserción
		if ($this->tenant_id && !isset($pedido_data['tenant_id'])) {
			$pedido_data['tenant_id'] = $this->tenant_id;
		}

		// Calcular total
		$total = 0;
		$items_validados = [];

		foreach ($items as $item) {
			// Validar que el producto existe, pertenece al tenant y está activo
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
	public function create($nombre, $telefono, $metodo_pago, $items)
	{
		$pedido_data = [
			'nombre_cliente' => $nombre,
			'telefono_cliente' => $telefono,
			'metodo_pago' => $metodo_pago,
			'estado' => 'pendiente'
		];

		return $this->create_with_items($pedido_data, $items);
	}

	/**
	 * Listar pedidos por tenant con paginación y filtros
	 * @param array $filters
	 * @return array
	 */
	public function get_all($filters = [])
	{
		$this->db->select('p.*, COUNT(pi.id) as total_items');
		$this->db->from('pedidos p');
		$this->db->join('pedido_items pi', 'pi.pedido_id = p.id', 'left');

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

		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

		return $this->db->get()->result();
	}

	/**
	 * Obtener pedido con items
	 * @param int $pedido_id
	 * @return object|null
	 */
	public function get_with_items($pedido_id)
	{
		$pedido = $this->db->get_where('pedidos', [
			'id' => $pedido_id,
			'tenant_id' => $this->tenant_id
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
	 * @param int $pedido_id
	 * @param string $estado
	 * @return bool
	 */
	public function update_estado($pedido_id, $estado)
	{
		$this->db->where('id', (int)$pedido_id);
		// Aplicar el scope del tenant actual para seguridad
		$this->applyTenantScope($this->db);
		$updated = $this->db->update('pedidos', ['estado' => $estado]);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Eliminar pedido y sus items
	 */
	public function delete_pedido($pedido_id)
	{
		$this->db->trans_start();

		// Verificar que el pedido pertenece al tenant
		$this->db->where('id', (int)$pedido_id);
		$this->applyTenantScope($this->db);
		$pedido = $this->db->get_where('pedidos', [
			'id' => (int)$pedido_id
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
	public function count($filters = [])
	{
		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

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
	public function get_stats($fecha_inicio = null, $fecha_fin = null)
	{
		// Aplicar el scope del tenant actual
		$this->applyTenantScope($this->db);

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

	/**
	 * Métodos chainables para consultas personalizadas
	 */
	public function where($field, $value)
	{
		$this->db->where($field, $value);
		return $this;
	}

	public function order_by($field, $direction = 'ASC')
	{
		$this->db->order_by($field, $direction);
		return $this;
	}

	public function limit($limit, $offset = 0)
	{
		$this->db->limit($limit, $offset);
		return $this;
	}

	/**
	 * Cuenta la cantidad de pedidos del tenant actual
	 * @param array $filters Filtros opcionales (fecha_inicio, fecha_fin, estado, etc.)
	 * @return int
	 */
	public function count_by_tenant($filters = [])
	{
		$this->applyTenantScope($this->db);
		
		// Aplicar filtros de fecha si existen
		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('creado_en >=', $filters['fecha_inicio']);
		}
		if (!empty($filters['fecha_fin'])) {
			$this->db->where('creado_en <=', $filters['fecha_fin']);
		}
		if (!empty($filters['estado'])) {
			$this->db->where('estado', $filters['estado']);
		}
		
		return $this->db->from('pedidos')->count_all_results();
	}

	/**
	 * Lista pedidos del tenant actual con filtros opcionales
	 * @param array $filters Filtros opcionales (limit, order_by, orden, fecha_inicio, fecha_fin, estado)
	 * @return array
	 */
	public function list_by_tenant($filters = [])
	{
		$this->db->select('p.*, COUNT(pi.id) as total_items');
		$this->db->from('pedidos p');
		$this->db->join('pedido_items pi', 'pi.pedido_id = p.id', 'left');
		
		// Aplicar scope de tenant
		$this->applyTenantScope($this->db);
		
		// Aplicar filtros
		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('p.creado_en >=', $filters['fecha_inicio']);
		}
		if (!empty($filters['fecha_fin'])) {
			$this->db->where('p.creado_en <=', $filters['fecha_fin']);
		}
		if (!empty($filters['estado'])) {
			$this->db->where('p.estado', $filters['estado']);
		}
		
		$this->db->group_by('p.id');
		
		// Ordenamiento
		$order_by = !empty($filters['order_by']) ? $filters['order_by'] : 'p.id';
		$orden = !empty($filters['orden']) ? $filters['orden'] : 'DESC';
		$this->db->order_by($order_by, $orden);
		
		// Límite
		if (!empty($filters['limit'])) {
			$this->db->limit($filters['limit']);
		}
		
		return $this->db->get()->result();
	}

	/**
	 * Obtener estadísticas globales de pedidos para dashboard admin
	 * @return array
	 */
	public function get_global_stats()
	{
		// Total de pedidos en el sistema
		$total = $this->db->count_all('pedidos');

		// Pedidos del mes actual
		$this->db->where('MONTH(creado_en)', date('m'));
		$this->db->where('YEAR(creado_en)', date('Y'));
		$mes_actual = $this->db->count_all_results('pedidos');

		// Pedidos por estado
		$this->db->select('estado, COUNT(*) as cantidad');
		$this->db->group_by('estado');
		$por_estado = $this->db->get('pedidos')->result();

		// Convertir a array asociativo
		$estados = [];
		foreach ($por_estado as $item) {
			$estados[$item->estado] = (int)$item->cantidad;
		}

		// Pedidos últimos 7 días
		$this->db->where('creado_en >=', date('Y-m-d', strtotime('-7 days')));
		$ultima_semana = $this->db->count_all_results('pedidos');

		// Promedio de pedidos por día del mes
		$dia_actual = (int)date('d');
		$promedio_diario = $dia_actual > 0 ? $mes_actual / $dia_actual : 0;

		return [
			'total' => $total,
			'mes_actual' => $mes_actual,
			'ultima_semana' => $ultima_semana,
			'por_estado' => $estados,
			'promedio_diario' => round($promedio_diario, 1)
		];
	}
}
