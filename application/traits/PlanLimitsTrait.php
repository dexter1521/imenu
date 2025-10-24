<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Trait PlanLimitsTrait
 *
 * Proporciona funcionalidad para validar límites del plan de suscripción
 * antes de permitir la creación de recursos (categorías, productos, etc.)
 *
 * @property CI_Loader $load
 * @property Tenant_model $tenant_model
 * @property Categoria_model $categoria_model
 * @property Producto_model $producto_model
 */
trait PlanLimitsTrait
{
    /**
     * Valida que no se exceda el límite del plan para un tipo de recurso
     *
     * @param int $tenant_id ID del tenant
     * @param string $tipo Tipo de recurso: 'categorias' o 'productos'
     * @return void
     * @throws Exception Si se excede el límite (termina con exit)
     */
    protected function enforce_limits($tenant_id, $tipo)
    {
        // Cargar modelos necesarios si no están cargados
        if (!isset($this->tenant_model)) {
            $this->load->model('Tenant_model', 'tenant_model');
        }

        // Obtener información del plan
        $plan = $this->tenant_model->get_with_plan($tenant_id);
        
        // Sin plan o plan sin límites = sin restricción
        if (!$plan) {
            return;
        }

        switch ($tipo) {
            case 'categorias':
                if (!isset($this->categoria_model)) {
                    $this->load->model('Categoria_model', 'categoria_model');
                }
                $count = $this->categoria_model->count_by_tenant();
                $limite = $plan->limite_categorias ?? null;
                $mensaje = 'Límite de categorías alcanzado';
                break;

            case 'productos':
                if (!isset($this->producto_model)) {
                    $this->load->model('Producto_model', 'producto_model');
                }
                $count = $this->producto_model->count_by_tenant();
                $limite = $plan->limite_productos ?? null;
                $mensaje = 'Límite de productos alcanzado';
                break;

            default:
                // Tipo no reconocido, no aplicar límite
                return;
        }

        // Verificar si se excede el límite
        if ($limite && $count >= $limite) {
            http_response_code(422);
            echo json_encode([
                'ok' => false, 
                'msg' => $mensaje,
                'limite' => $limite,
                'actual' => $count
            ]);
            exit;
        }
    }
}
