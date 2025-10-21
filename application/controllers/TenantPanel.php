<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class AdminPanel
 * Controlador para las páginas públicas del panel de administración (SaaS).
 * No hereda de MY_Controller porque maneja rutas no autenticadas como el login.
 */
class TenantPanel extends CI_Controller
{
	public function login()
	{
		$this->load->view('app/login');
	}
}
