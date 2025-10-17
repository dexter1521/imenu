<?php defined('BASEPATH') or exit('No direct script access allowed');

class AdminPanel extends MY_Controller
{
    public function login()
    {
        //$this->render_admin_template('admin/login');
		$this->load->view('admin/login');
    }
}
