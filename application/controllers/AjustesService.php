<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/PlanLimitsTrait.php';

class AjustesService extends MY_Controller
{
	use PlanLimitsTrait;

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
		$this->load->model('Ajustes_model', 'ajustes_model');
		$this->load->model('Tenant_model', 'tenant_model');
	}

	// ===== Ajustes =====
	public function ajustes_get()
	{
		try {
			$row = $this->ajustes_model->get_by_tenant();

			// Si no hay ajustes, crear valores por defecto
			if (!$row) {
				$row = (object)[
					'nombre_negocio' => '',
					'telefono' => '',
					'email' => '',
					'direccion' => '',
					'color_primario' => '#F50087',
					'mostrar_precios' => 1,
					'mostrar_imagenes' => 1,
					'aceptar_pedidos' => 1,
					'idioma' => 'es',
					'moneda' => 'MXN',
					'formato_precio' => '$0.00',
					'zona_horaria' => 'America/Mexico_City',
					'mensaje_bienvenida' => '',
					'notas_menu' => '',
					'mensaje_pedido' => '',
					'pie_menu' => ''
				];
			}

			echo json_encode(['ok' => true, 'data' => $row]);
		} catch (Exception $e) {
			log_message('error', 'Error en ajustes_get: ' . $e->getMessage());
			http_response_code(500);
			echo json_encode(['ok' => false, 'msg' => 'Error al cargar ajustes']);
		}
	}

	public function ajustes_update()
	{
		$tid = current_tenant_id();
		$data = [];

		// Información general
		$generalFields = [
			'nombre_negocio',
			'telefono',
			'email',
			'direccion'
		];

		// Personalización visual
		$visualFields = [
			'color_primario',
			'logo_url',
			'mostrar_precios',
			'mostrar_imagenes',
			'aceptar_pedidos'
		];

		// Configuración regional
		$regionalFields = [
			'idioma',
			'moneda',
			'formato_precio',
			'zona_horaria'
		];

		// Mensajes personalizados
		$messageFields = [
			'mensaje_bienvenida',
			'notas_menu',
			'mensaje_pedido',
			'pie_menu'
		];

		// Horarios (7 días x 3 campos)
		$horarioFields = [];
		$dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
		foreach ($dias as $dia) {
			$horarioFields[] = $dia . '_abierto';
			$horarioFields[] = $dia . '_inicio';
			$horarioFields[] = $dia . '_fin';
		}

		// Unir todos los campos
		$allFields = array_merge($generalFields, $visualFields, $regionalFields, $messageFields, $horarioFields);

		// Recolectar datos del POST
		foreach ($allFields as $k) {
			if (null !== ($v = $this->input->post($k))) {
				$data[$k] = $v;
			}
		}

		// Guardar
		$this->ajustes_model->upsert($data);
		echo json_encode(['ok' => true]);
	}

	// ===== Configuración de Notificaciones =====

	public function notificaciones_config()
	{
		header('Content-Type: application/json');

		// Verificar permisos de owner
		if (current_role() !== 'owner') {
			$this->_api_error(403, 'Solo el owner puede configurar notificaciones');
			return;
		}

		$tid = current_tenant_id();

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// Obtener configuración actual
			$config = $this->tenant_model->get_notification_config($tid);

			echo json_encode([
				'ok' => true,
				'data' => $config ?: [
					'notif_email' => null,
					'notif_webhook' => null,
					'notif_whatsapp' => null
				]
			]);
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Actualizar configuración
			$notif_email = $this->input->post('notif_email', true);
			$notif_webhook = $this->input->post('notif_webhook', true);
			$notif_whatsapp = $this->input->post('notif_whatsapp', true);

			// Validaciones
			if (!empty($notif_email) && !filter_var($notif_email, FILTER_VALIDATE_EMAIL)) {
				$this->_api_error(400, 'Email inválido');
				return;
			}

			if (!empty($notif_webhook) && !filter_var($notif_webhook, FILTER_VALIDATE_URL)) {
				$this->_api_error(400, 'URL de webhook inválida');
				return;
			}

			if (!empty($notif_whatsapp) && !preg_match('/^\+?[1-9]\d{1,14}$/', $notif_whatsapp)) {
				$this->_api_error(400, 'Número de WhatsApp inválido');
				return;
			}

			// Actualizar usando el modelo
			$update_data = [
				'notif_email' => $notif_email ?: null,
				'notif_webhook' => $notif_webhook ?: null,
				'notif_whatsapp' => $notif_whatsapp ?: null
			];

			try {
				$this->tenant_model->update_notification_config($tid, $update_data);
				echo json_encode(['ok' => true, 'msg' => 'Configuración actualizada']);
			} catch (Exception $e) {
				log_message('error', 'Error actualizando config notificaciones: ' . $e->getMessage());
				$this->_api_error(500, 'Error del servidor');
			}
		} else {
			$this->_api_error(405, 'Método no permitido');
		}
	}
}
