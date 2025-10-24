<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'traits/TenantScope.php';


class Ajustes_model extends CI_Model
{
       use TenantScope {
	       TenantScope::__construct as private __tenantScopeConstruct;
       }

       public function __construct()
       {
	       parent::__construct();
	       $this->load->database();
	       $this->__tenantScopeConstruct();
       }

       /**
	* Obtener ajustes del tenant actual
	* @return object|null
	*/
       public function get_by_tenant()
       {
	       $this->applyTenantScope($this->db);
	       return $this->db->get('ajustes')->row();
       }

       /**
	* Crear ajustes por defecto para el tenant actual
	* @return int ID de ajustes creado
	*/
       public function create_default()
       {
	       $defaults = [
		       'tenant_id' => $this->tenant_id,
		       'idioma' => 'es',
		       'moneda' => 'MXN',
		       'formato_precio' => '$0.00',
		       'show_precios' => 1,
		       'show_imgs' => 1
	       ];
	       $this->db->insert('ajustes', $defaults);
	       return $this->db->insert_id();
       }

       /**
	* Actualizar o insertar ajustes (upsert) para el tenant actual
	* @param array $data
	* @return bool
	*/
       public function upsert($data)
       {
	       $exists = $this->get_by_tenant();

	       if ($exists) {
		       // Actualizar existente
		       $this->applyTenantScope($this->db);
		       return $this->db->update('ajustes', $data);
	       } else {
		       // Insertar nuevo
		       $data['tenant_id'] = $this->tenant_id;
		       $this->db->insert('ajustes', $data);
		       return $this->db->insert_id() > 0;
	       }
       }
}
