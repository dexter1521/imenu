<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Trait TenantScope
 *
 * Aplica automáticamente un scope de 'tenant_id' a las consultas de los modelos
 * para prevenir fugas de datos en una arquitectura multi-tenant.
 *
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 */
trait TenantScope
{
    /**
     * @var int|null El ID del tenant actual.
     */
    protected $tenant_id;

    /**
     * El constructor del trait se encarga de obtener el tenant_id
     * automáticamente desde el helper de autenticación al instanciar el modelo.
     */
    public function __construct()
    {
        parent::__construct();

        // El helper 'auth' se carga globalmente, pero lo verificamos por si acaso.
        if (!function_exists('current_role')) {
            $this->load->helper('auth');
        }

        // El rol 'admin' no tiene scope de tenant, puede ver todos los datos.
        if (current_role() !== 'admin') {
            $this->tenant_id = current_tenant_id();
        }
    }

    /**
     * Aplica la condición WHERE tenant_id a una instancia del Query Builder.
     *
     * @param CI_DB_query_builder $db Instancia del Query Builder de CodeIgniter, pasada por referencia.
     */
    protected function applyTenantScope(&$db)
    {
        if (!empty($this->tenant_id)) {
            $db->where('tenant_id', $this->tenant_id);
        }
    }

    /**
     * Permite establecer manualmente el tenant_id. Útil para tareas de admin o scripts.
     *
     * @param int $tenant_id
     */
    public function setTenantId($tenant_id)
    {
        $this->tenant_id = $tenant_id;
    }

    /**
     * Obtiene el tenant_id actualmente en scope.
     * @return int|null
     */
    public function getTenantId()
    {
        return $this->tenant_id;
    }
}
