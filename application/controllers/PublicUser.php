<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PublicUser extends CI_Controller
{

	public function __construct() // Name expected.
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Tenant_model', 'tenant_model');
		$this->load->model('Categoria_model', 'categoria_model');
		$this->load->model('Producto_model', 'producto_model');
		$this->load->model('Ajustes_model', 'ajustes_model');
		$this->load->model('Pedido_model', 'pedido_model');
	}

	// Vista HTML: /r/{slug}
	public function menu($slug)
	{
		$tenant = $this->tenant_model->get_by_slug_active($slug);
		if (!$tenant) show_404();
		
		// Establecer el tenant_id manualmente en los modelos para contexto público
		$this->categoria_model->setTenantId($tenant->id);
		$this->producto_model->setTenantId($tenant->id);
		$this->ajustes_model->setTenantId($tenant->id);
		
		// Obtener categorías y productos activos
		$cats = $this->categoria_model->get_all(true); // true = solo activos
		$prods = $this->producto_model->get_all(true); // true = solo activos
		$aj   = $this->ajustes_model->get_by_tenant();
		
		$data = compact('tenant', 'cats', 'prods', 'aj');
		$this->load->view('public/menu', $data);
	}

	// API JSON: /api/public/menu?slug=india-bonita
	public function api_menu()
	{
		$this->output->set_content_type('application/json');
		$slug = $this->input->get('slug');
		if (!$slug) return $this->output->set_status_header(400)->set_output(json_encode(['ok' => false, 'msg' => 'slug requerido']));
		$t = $this->tenant_model->get_by_slug_active($slug);
		if (!$t) return $this->output->set_status_header(404)->set_output(json_encode(['ok' => false, 'msg' => 'tenant no encontrado']));
		
		// Establecer el tenant_id manualmente en los modelos para contexto público
		$this->categoria_model->setTenantId($t->id);
		$this->producto_model->setTenantId($t->id);
		
		// Obtener categorías y productos activos
		$cats = $this->categoria_model->get_all(true); // true = solo activos
		$prods = $this->producto_model->get_all(true); // true = solo activos
		
		return $this->output->set_output(json_encode(['ok' => true, 'tenant' => $t, 'categorias' => $cats, 'productos' => $prods]));
	}


	public function crear_pedido()
	{
		$this->output->set_content_type('application/json');

		$slug   = $this->input->post('slug', true);
		$nombre = $this->input->post('nombre', true);
		$tel    = $this->input->post('telefono', true);
		$metodo = $this->input->post('metodo_pago', true) ?: 'efectivo';
		$itemsJ = $this->input->post('items'); // JSON: [{producto_id, cantidad}]

		if (!$slug || !$nombre || !$tel || !$itemsJ) {
			return $this->output->set_status_header(400)
				->set_output(json_encode(['ok' => false, 'msg' => 'Datos incompletos']));
		}

		$tenant = $this->tenant_model->get_by_slug_active($slug);
		if (!$tenant) return $this->output->set_status_header(404)
			->set_output(json_encode(['ok' => false, 'msg' => 'Tenant no encontrado']));

		// Establecer el tenant_id manualmente para contexto público
		$this->producto_model->setTenantId($tenant->id);
		$this->pedido_model->setTenantId($tenant->id);

		$items = json_decode($itemsJ, true);
		if (!is_array($items) || empty($items)) {
			return $this->output->set_status_header(400)
				->set_output(json_encode(['ok' => false, 'msg' => 'Items inválidos']));
		}

		try {
			// El método create ya no requiere tenant_id como primer parámetro
			$pedido_id = $this->pedido_model->create($nombre, $tel, $metodo, $items);
		} catch (Exception $e) {
			return $this->output->set_status_header(422)
				->set_output(json_encode(['ok' => false, 'msg' => $e->getMessage()]));
		}

		// WhatsApp
		$waNum = preg_replace('/\D+/', '', (string)$tenant->whatsapp);
		$lineas = [];
		$lineas[] = 'Nuevo pedido - ' . $tenant->nombre;
		$lineas[] = 'Cliente: ' . $nombre . ' (' . $tel . ')';
		$lineas[] = 'Pago: ' . $metodo;
		$lineas[] = '— — —';
		$total = 0;
		foreach ($items as $it) {
			$pid = (int)$it['producto_id'];
			$qty = (int)$it['cantidad'];
			$p = $this->producto_model->get_by_id($pid);
			if (!$p) continue;
			$sub = ((float)$p->precio) * $qty;
			$total += $sub;
			$lineas[] = $qty . ' x ' . $p->nombre . ' - $' . number_format($sub, 2);
		}
		$lineas[] = 'Total: $' . number_format($total, 2);
		$msg = implode("\n", $lineas);
		$waUrl = $waNum ? ('https://wa.me/52' . $waNum . '?text=' . rawurlencode($msg)) : null;

		// Obtener nuevo token CSRF
		$csrf = array(
			'name' => $this->config->item('csrf_token_name'),
			'hash' => $this->security->get_csrf_hash()
		);

		return $this->output->set_output(json_encode([
			'ok' => true,
			'pedido_id' => $pedido_id,
			'whatsapp_url' => $waUrl,
			'csrf_token' => $csrf['hash']
		]));
	}
}
