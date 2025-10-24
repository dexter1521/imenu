<?php defined('BASEPATH') or exit('No direct script access allowed');


class PagosService extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('auth');

		// Validar JWT directamente (no depender de AuthHook)
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
				redirect('/adminpanel/login?expired=1');
				exit;
			}
		}

		// Verificar que el usuario tenga rol de admin
		$rol = current_role();

		if ($rol !== 'admin') {
			if ($this->input->is_ajax_request()) {
				$this->_api_error(403, 'Acceso denegado: se requiere rol de administrador');
			} else {
				redirect('/adminpanel/login?expired=1');
			}
			exit;
		}

		// Cargar modelos necesarios
		$this->load->model('Pago_model', 'pago_model');
	}

	// ===== PAGOS =====

	/**
	 * Listar pagos con filtros opcionales
	 * GET /admin/pagos
	 * Query params: tenant_id, status, metodo, fecha_inicio, fecha_fin, concepto
	 */
	public function pagos()
	{
		// Obtener parámetros de filtro
		$filters = [
			'tenant_id' => $this->input->get('tenant_id'),
			'status' => $this->input->get('status'),
			'metodo' => $this->input->get('metodo'),
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin'),
			'concepto' => $this->input->get('concepto')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		// Obtener pagos con filtros
		$rows = $this->pago_model->get_with_filters($filters);

		echo json_encode(['ok' => true, 'data' => $rows]);
	}

	/**
	 * Obtener estadísticas de pagos
	 * GET /admin/pago_stats
	 * Query params: fecha_inicio, fecha_fin, tenant_id
	 */
	public function pago_stats()
	{
		$filters = [
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin'),
			'tenant_id' => $this->input->get('tenant_id')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		$stats = $this->pago_model->get_stats($filters);

		echo json_encode(['ok' => true, 'data' => $stats]);
	}

	/**
	 * Obtener detalles completos de un pago
	 * GET /admin/pago_detail/[id]
	 */
	public function pago_detail($id)
	{
		if (!$id) {
			$this->_api_error(400, 'ID de pago requerido');
			return;
		}

		$pago = $this->pago_model->get_with_relations((int)$id);

		if (!$pago) {
			$this->_api_error(404, 'Pago no encontrado');
			return;
		}

		echo json_encode(['ok' => true, 'data' => $pago]);
	}

	/**
	 * Exportar pagos a CSV o Excel
	 * GET /admin/pago_export
	 * Query params: formato (csv|excel), fecha_inicio, fecha_fin, tenant_id, status, metodo
	 */
	public function pago_export()
	{
		$formato = $this->input->get('formato') ?: 'csv';

		// Obtener filtros
		$filters = [
			'tenant_id' => $this->input->get('tenant_id'),
			'status' => $this->input->get('status'),
			'metodo' => $this->input->get('metodo'),
			'fecha_inicio' => $this->input->get('fecha_inicio'),
			'fecha_fin' => $this->input->get('fecha_fin')
		];

		// Remover filtros vacíos
		$filters = array_filter($filters, function ($val) {
			return $val !== null && $val !== '';
		});

		// Obtener datos
		$pagos = $this->pago_model->get_with_filters($filters);

		if ($formato === 'csv') {
			$this->_export_csv($pagos);
		} elseif ($formato === 'excel') {
			$this->_export_excel($pagos);
		} else {
			$this->_api_error(400, 'Formato no soportado. Use csv o excel');
		}
	}

	/**
	 * Exportar a CSV
	 */
	private function _export_csv($pagos)
	{
		$filename = 'pagos_' . date('Y-m-d_His') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		$output = fopen('php://output', 'w');

		// BOM para UTF-8 en Excel
		fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

		// Encabezados
		fputcsv($output, [
			'ID',
			'Tenant',
			'Slug',
			'Concepto',
			'Monto',
			'Método',
			'Referencia',
			'Estado',
			'Fecha',
			'Notas'
		]);

		// Datos
		foreach ($pagos as $pago) {
			fputcsv($output, [
				$pago->id,
				$pago->tenant_nombre ?? 'N/A',
				$pago->tenant_slug ?? 'N/A',
				$pago->concepto,
				number_format($pago->monto, 2),
				$pago->metodo,
				$pago->referencia ?? '',
				$pago->status,
				$pago->fecha,
				$pago->notas ?? ''
			]);
		}

		fclose($output);
		exit;
	}

	/**
	 * Exportar a Excel (CSV con formato mejorado)
	 */
	private function _export_excel($pagos)
	{
		$filename = 'pagos_' . date('Y-m-d_His') . '.xls';

		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
		echo '<head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
		echo '<x:Name>Pagos</x:Name>';
		echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>';
		echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
		echo '</head>';
		echo '<body>';
		echo '<table border="1">';

		// Encabezados
		echo '<thead><tr style="background-color: #4e73df; color: white; font-weight: bold;">';
		echo '<th>ID</th>';
		echo '<th>Tenant</th>';
		echo '<th>Slug</th>';
		echo '<th>Concepto</th>';
		echo '<th>Monto</th>';
		echo '<th>Método</th>';
		echo '<th>Referencia</th>';
		echo '<th>Estado</th>';
		echo '<th>Fecha</th>';
		echo '<th>Notas</th>';
		echo '</tr></thead>';

		// Datos
		echo '<tbody>';
		foreach ($pagos as $pago) {
			$statusColor = '';
			if ($pago->status === 'pagado') $statusColor = '#28a745';
			elseif ($pago->status === 'pendiente') $statusColor = '#ffc107';
			elseif ($pago->status === 'fallido') $statusColor = '#dc3545';

			echo '<tr>';
			echo '<td>' . htmlspecialchars($pago->id) . '</td>';
			echo '<td>' . htmlspecialchars($pago->tenant_nombre ?? 'N/A') . '</td>';
			echo '<td>' . htmlspecialchars($pago->tenant_slug ?? 'N/A') . '</td>';
			echo '<td>' . htmlspecialchars($pago->concepto) . '</td>';
			echo '<td style="text-align: right;">$' . number_format($pago->monto, 2) . '</td>';
			echo '<td>' . htmlspecialchars($pago->metodo) . '</td>';
			echo '<td>' . htmlspecialchars($pago->referencia ?? '') . '</td>';
			echo '<td style="background-color: ' . $statusColor . '; color: white;">' . htmlspecialchars($pago->status) . '</td>';
			echo '<td>' . htmlspecialchars($pago->fecha) . '</td>';
			echo '<td>' . htmlspecialchars($pago->notas ?? '') . '</td>';
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table>';
		echo '</body>';
		echo '</html>';

		exit;
	}
}
