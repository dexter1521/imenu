<?php defined('BASEPATH') or exit('No direct script access allowed');

class PedidosService extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Obtener el método que se está ejecutando
		$method = $this->router->fetch_method();

		// Métodos públicos que no requieren autenticación
		$public_methods = ['login'];

		// Si es un método público, no validar JWT
		if (in_array($method, $public_methods)) {
			return;
		}

		// Validar JWT directamente para métodos protegidos
		try {
			jwt_require(); // Esto establece $this->jwt
		} catch (Exception $e) {
			// JWT inválido o no existe
			if ($this->input->is_ajax_request()) {
				$this->output
					->set_status_header(401)
					->set_content_type('application/json')
					->set_output(json_encode(['ok' => false, 'msg' => 'No autenticado']))
					->_display();
				exit;
			} else {
				redirect('/app/login?expired=1');
				exit;
			}
		}

		// Verificar que el usuario tenga un tenant_id válido
		$tenant_id = current_tenant_id();
		if (!$tenant_id) {
			if ($this->input->is_ajax_request()) {
				$this->output
					->set_status_header(403)
					->set_content_type('application/json')
					->set_output(json_encode(['ok' => false, 'msg' => 'Sin tenant asociado']))
					->_display();
				exit;
			} else {
				redirect('/app/login?expired=1');
				exit;
			}
		}
		// Cargar modelos necesarios con alias en minúsculas
		$this->load->model('Pedido_model', 'pedido_model');
	}

	public function pedidos()
	{
		header('Content-Type: application/json');

		$tid = current_tenant_id();

		// Obtener filtros
		$filters = [
			'estado' => $this->input->get('estado', true),
			'fecha_inicio' => $this->input->get('fecha_inicio', true),
			'fecha_fin' => $this->input->get('fecha_fin', true),
			'cliente' => $this->input->get('cliente', true),
			'metodo_pago' => $this->input->get('metodo_pago', true),
			'limit' => (int)$this->input->get('limit') ?: 50,
			'offset' => (int)$this->input->get('offset') ?: 0,
			'orden' => $this->input->get('orden', true) ?: 'desc'
		];

		// Validar filtros
		if (!empty($filters['estado'])) {
			$estados_validos = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
			if (!in_array($filters['estado'], $estados_validos)) {
				$this->_api_error(400, 'Estado inválido');
				return;
			}
		}

		if ($filters['limit'] > 100) {
			$filters['limit'] = 100; // Límite máximo para evitar sobrecarga
		}

		try {
			$rows = $this->pedido_model->list_by_tenant($tid, $filters);

			// Contar total para paginación
			$total = $this->pedido_model->count_by_tenant($tid, $filters);

			echo json_encode([
				'ok' => true,
				'data' => $rows,
				'pagination' => [
					'total' => $total,
					'limit' => $filters['limit'],
					'offset' => $filters['offset'],
					'has_more' => ($filters['offset'] + $filters['limit']) < $total
				]
			]);
		} catch (Exception $e) {
			log_message('error', 'Error listando pedidos: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido_create()
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		$tid = current_tenant_id();
		$nombre_cliente = $this->input->post('nombre_cliente', true);
		$telefono_cliente = $this->input->post('telefono_cliente', true);
		$items = $this->input->post('items'); // JSON array

		// Validaciones de entrada
		if (empty($nombre_cliente) || empty($items)) {
			$this->_api_error(400, 'nombre_cliente e items son requeridos');
			return;
		}

		if (strlen($nombre_cliente) > 100) {
			$this->_api_error(400, 'nombre_cliente demasiado largo');
			return;
		}

		if (!empty($telefono_cliente) && strlen($telefono_cliente) > 20) {
			$this->_api_error(400, 'telefono_cliente demasiado largo');
			return;
		}

		// Validar y decodificar items
		if (is_string($items)) {
			$items = json_decode($items, true);
		}

		if (!is_array($items) || empty($items)) {
			$this->_api_error(400, 'items debe ser un array válido');
			return;
		}

		if (count($items) > 50) {
			$this->_api_error(400, 'Demasiados items en el pedido');
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$pedido_data = [
				'tenant_id' => $tid,
				'nombre_cliente' => $nombre_cliente,
				'telefono_cliente' => $telefono_cliente,
				'metodo_pago' => $this->input->post('metodo_pago', true) ?: 'efectivo',
				'estado' => 'pendiente'
			];

			$pedido_id = $this->pedido_model->create_with_items($pedido_data, $items);

			if ($pedido_id) {
				// Trigger notification for new order
				$this->load->library('Notification_lib');
				$this->Notification_lib->notify_new_order($pedido_id, $tid);

				echo json_encode(['ok' => true, 'id' => $pedido_id]);
			} else {
				$this->_api_error(500, 'Error al crear el pedido');
			}
		} catch (Exception $e) {
			log_message('error', 'Error creando pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido($id)
	{
		header('Content-Type: application/json');

		$tid = current_tenant_id();
		$this->load->model('Pedido_model');
		$row = $this->pedido_model->get_with_items($tid, (int)$id);

		if (!$row) {
			$this->_api_error(404, 'Pedido no encontrado');
			return;
		}

		// Verificar que el pedido pertenece al tenant actual
		if (!$this->_validate_tenant_access($row->tenant_id)) {
			return;
		}

		echo json_encode(['ok' => true, 'data' => $row]);
	}

	public function pedido_update_estado($id)
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		$tid = current_tenant_id();
		$estado = $this->input->post('estado', true);

		// Validar entrada
		if (empty($estado)) {
			$this->_api_error(400, 'Estado es requerido');
			return;
		}

		$estados_validos = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
		if (!in_array($estado, $estados_validos)) {
			$this->_api_error(400, 'Estado inválido. Valores permitidos: ' . implode(', ', $estados_validos));
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$updated = $this->pedido_model->update_estado($tid, (int)$id, $estado);

			if ($updated) {
				echo json_encode(['ok' => true, 'msg' => 'Estado actualizado']);
			} else {
				$this->_api_error(404, 'Pedido no encontrado');
			}
		} catch (Exception $e) {
			log_message('error', 'Error actualizando estado de pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	public function pedido_delete($id)
	{
		header('Content-Type: application/json');

		// Verificar método HTTP
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
			$this->_api_error(405, 'Método no permitido');
			return;
		}

		// Solo owner puede eliminar pedidos
		if (current_role() !== 'owner') {
			$this->_api_error(403, 'Solo el owner puede eliminar pedidos');
			return;
		}

		$tid = current_tenant_id();

		// Validar ID
		$pedido_id = (int)$id;
		if ($pedido_id <= 0) {
			$this->_api_error(400, 'ID de pedido inválido');
			return;
		}

		try {
			$this->load->model('Pedido_model');
			$deleted = $this->pedido_model->delete_pedido($tid, $pedido_id);

			if ($deleted) {
				echo json_encode(['ok' => true, 'msg' => 'Pedido eliminado']);
			} else {
				$this->_api_error(404, 'Pedido no encontrado');
			}
		} catch (Exception $e) {
			log_message('error', 'Error eliminando pedido: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	// ===== Exportación de Datos =====

	public function pedidos_export()
	{
		$tid = current_tenant_id();
		$formato = $this->input->get('formato', true) ?: 'csv';

		// Validar formato
		if (!in_array($formato, ['csv', 'json', 'excel'])) {
			$this->_api_error(400, 'Formato no soportado. Use: csv, json, excel');
			return;
		}

		// Obtener filtros (mismos que en pedidos())
		$filters = [
			'estado' => $this->input->get('estado', true),
			'fecha_inicio' => $this->input->get('fecha_inicio', true),
			'fecha_fin' => $this->input->get('fecha_fin', true),
			'cliente' => $this->input->get('cliente', true),
			'metodo_pago' => $this->input->get('metodo_pago', true),
			'limit' => 1000, // Límite para exportación
			'orden' => 'desc'
		];

		try {
			$pedidos = $this->pedido_model->list_by_tenant($tid, $filters);

			if (empty($pedidos)) {
				$this->_api_error(404, 'No hay pedidos para exportar');
				return;
			}

			switch ($formato) {
				case 'csv':
					$this->_export_csv($pedidos);
					break;
				case 'json':
					$this->_export_json($pedidos);
					break;
				case 'excel':
					$this->_export_excel($pedidos);
					break;
			}
		} catch (Exception $e) {
			log_message('error', 'Error exportando pedidos: ' . $e->getMessage());
			$this->_api_error(500, 'Error del servidor');
		}
	}

	private function _export_csv($pedidos)
	{
		$filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		$output = fopen('php://output', 'w');

		// Encabezados
		fputcsv($output, [
			'ID',
			'Cliente',
			'Teléfono',
			'Total',
			'Método Pago',
			'Estado',
			'Items',
			'Fecha Creación'
		]);

		// Datos
		foreach ($pedidos as $pedido) {
			fputcsv($output, [
				$pedido->id,
				$pedido->nombre_cliente,
				$pedido->telefono_cliente,
				$pedido->total,
				$pedido->metodo_pago,
				$pedido->estado,
				$pedido->total_items,
				$pedido->creado_en
			]);
		}

		fclose($output);
	}

	private function _export_json($pedidos)
	{
		$filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.json';

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo json_encode([
			'exported_at' => date('c'),
			'total_records' => count($pedidos),
			'data' => $pedidos
		], JSON_PRETTY_PRINT);
	}

	private function _export_excel($pedidos)
	{
		// Fallback a CSV si no hay librería Excel
		$this->_export_csv($pedidos);
	}
}
