<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PublicUser extends CI_Controller
{

	public function __construct() // Name expected.
	{
		parent::__construct();
		$this->load->database();
	}

	// Vista HTML: /r/{slug}
	public function menu($slug)
	{
		$tenant = $this->db->get_where('tenants', ['slug' => $slug, 'activo' => 1], 1)->row();
		if (!$tenant) show_404();
		$this->db->order_by('orden');
		$cats = $this->db->get_where('categorias', ['tenant_id' => $tenant->id, 'activo' => 1])->result();
		$this->db->order_by('orden');
		$prods = $this->db->get_where('productos', ['tenant_id' => $tenant->id, 'activo' => 1])->result();
		$aj   = $this->db->get_where('ajustes', ['tenant_id' => $tenant->id], 1)->row();
		$data = compact('tenant', 'cats', 'prods', 'aj');
		$this->load->view('public/menu', $data);
	}

	// API JSON: /api/public/menu?slug=india-bonita
	public function api_menu()
	{
		$this->output->set_content_type('application/json');
		$slug = $this->input->get('slug');
		if (!$slug) return $this->output->set_status_header(400)->set_output(json_encode(['ok' => false, 'msg' => 'slug requerido']));
		$t = $this->db->get_where('tenants', ['slug' => $slug, 'activo' => 1], 1)->row();
		if (!$t) return $this->output->set_status_header(404)->set_output(json_encode(['ok' => false, 'msg' => 'tenant no encontrado']));
		$this->db->order_by('orden');
		$cats = $this->db->get_where('categorias', ['tenant_id' => $t->id, 'activo' => 1])->result();
		$this->db->order_by('orden');
		$prods = $this->db->get_where('productos', ['tenant_id' => $t->id, 'activo' => 1])->result();
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

		$tenant = $this->db->get_where('tenants', ['slug' => $slug, 'activo' => 1], 1)->row();
		if (!$tenant) return $this->output->set_status_header(404)
			->set_output(json_encode(['ok' => false, 'msg' => 'Tenant no encontrado']));

		$items = json_decode($itemsJ, true);
		if (!is_array($items) || empty($items)) {
			return $this->output->set_status_header(400)
				->set_output(json_encode(['ok' => false, 'msg' => 'Items inválidos']));
		}

		$this->load->model('Pedido_model');
		try {
			$pedido_id = $this->Pedido_model->create($tenant->id, $nombre, $tel, $metodo, $items);
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
			$p = $this->db->get_where('productos', ['id' => $pid], 1)->row();
			if (!$p) continue;
			$sub = ((float)$p->precio) * $qty;
			$total += $sub;
			$lineas[] = $qty . ' x ' . $p->nombre . ' - $' . number_format($sub, 2);
		}
		$lineas[] = 'Total: $' . number_format($total, 2);
		$msg = implode("\n", $lineas);
		$waUrl = $waNum ? ('https://wa.me/52' . $waNum . '?text=' . rawurlencode($msg)) : null;

		return $this->output->set_output(json_encode([
			'ok' => true,
			'pedido_id' => $pedido_id,
			'whatsapp_url' => $waUrl
		]));
	}
}
